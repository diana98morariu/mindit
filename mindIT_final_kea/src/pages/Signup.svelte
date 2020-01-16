<script>
	import jq from "jquery";
	import toastr from "toastr";
    import { curRoute } from '../routing/router.js';

	const basicURL = '../../../backend/mindIT_backend/apis/';
	let userData = {
        firstName: '', lastName: '', username: '', email: '', password: '', cardNumber: '', CVV: '', expDate: ''
    };

	let smthWasTouched = false, firstNameWasTouched = false, lastNameWasTouched = false, usernameWasTouched = false, passwordWasTouched = false,
		emailWasTouched = false, cardNumberWasTouched = false, expDateWasTouched = false, CVVWasTouched = false, triedWithEmpty = true;

    toastr.options = {
		"positionClass": "toast-bottom-right",
		"preventDuplicates": true,
    }

    function validateEmail(email) {
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }

    const swichToLoginPage = () => {
        curRoute.set('/login');
        window.history.pushState({path: '/login'}, '', window.location.origin + '/login');
    }
	
	const setFirstTouched = (input) => {
		smthWasTouched = true;
		if(input === 'firstName') {firstNameWasTouched = true};
		if(input === 'lastName') {lastNameWasTouched = true};
		if(input === 'username') {usernameWasTouched = true};
		if(input === 'password') {passwordWasTouched = true};
		if(input === 'email') {emailWasTouched = true};
		if(input === 'cardNumber') {cardNumberWasTouched = true};
		if(input === 'expDate') {expDateWasTouched = true};
		if(input === 'CVV') {CVVWasTouched = true};

	}

	const validateInput = (elmValue, input) => {
		let isValid = false;
		triedWithEmpty = false;
		switch(input) {
			case 'firstName':
				isValid = elmValue.replace(/ /g,'').length > 1 && /^[a-zA-Z]*$/g.test(elmValue.replace(/ /g,''));
				break;
			case 'lastName':
				isValid = elmValue.replace(/ /g,'').length > 1 && /^[a-zA-Z]*$/g.test(elmValue.replace(/ /g,''));
				break;
			case 'username':
				isValid = elmValue.replace(/ /g,'').length > 5;
				break;
			case 'password':
				isValid = elmValue.length > 5;
                break;
            case 'email':
                isValid = elmValue.length > 5 && validateEmail(elmValue);
                break;
			case 'cardNumber':
				isValid = (elmValue.replace(/ /g,'').length === 16 && /^\d+$/.test(elmValue.replace(/ /g,''))) ? true : false;
				break;
			case 'expDate':
				isValid = (elmValue.replace(/ /g,'').length === 7 && /^(0[1-9]|1[0-2])\/?([0-9]{4}|[0-9]{2})$/.test(elmValue.replace(/ /g,'')) && parseInt(elmValue.replace(/ /g,'').split('/')[1]) >= new Date().getFullYear());
				break;
			case 'CVV':
				isValid = (elmValue.replace(/ /g,'').length === 3 && /^\d+$/.test(elmValue.replace(/ /g,''))) ? true : false;
				break;
			default:
				// console.log(`VALIDATION FAILED: no validation for: ${input}`);
				break;
		}
		return isValid;
	}

	const areThereErrors = () => { return jq('.error').length > 0 ? true : false }

	const signup = () => {
        if(areThereErrors()) {
            toastr.error('Please make sure all the fields are completed and valid!');
            return;
        }
        
		if(smthWasTouched) {
            if(!(parseInt(userData.expDate.replace(/ /g,'').split('/')[1]) >= new Date().getFullYear())){
                toastr.error('Your card is expired! Please use a calid card');
                return;
            }

            for(let key in userData) {
                if(userData[key] === "") {
                    toastr.error(`Field ${key.toLowerCase()} can not be empty!`);
                    return;
                }
            }

            const card_expMonth = userData.expDate.split('/')[0];
            const card_expYear = userData.expDate.split('/')[1];
            userData.cardNumber = userData.cardNumber.trim();
			jq.ajax({
				type: "POST",
				url: basicURL + "api-signup.php",
				dataType: "json",
				data: {
                    user_firstName: userData.firstName,
                    user_lastName: userData.lastName,
                    user_email: userData.email,
                    user_password: userData.password,
                    user_username: userData.username,
                    card_number: userData.cardNumber,
                    card_expMonth: card_expMonth,
                    card_expYear: card_expYear,
                    card_CVV: userData.CVV
				},
				success: (data) => {
                    // console.log(data);
                    if(data.status === 1) {
                        toastr.success('Your account has been created! You can login now');
                        curRoute.set('/login');
                        window.history.pushState({path: '/login'}, '', window.location.origin + '/login');
                    } else if (data.message === 'user already existent'){
                        toastr.info('Already taken! Please choose another username or email!');
                    } else if (data.message === 'Insufficient amount to transfer - cannot create first payment'){
                        toastr.info('Please use a credit card which has more than 20kr as balance!');
                    } else if (data.message === 'userPassword too short'){
                        toastr.error('Your password is not long enough! You need to have at least 6 characters')
                    }
				},
				error: (err) => {
					// console.log(err);
				}
			});
		} else {
            toastr.error('Please complete form fields!')
        }
    };

</script>

<style>
    .background{
        background: #eeeeee78;
        width: 100vw;
        height: 100vh;
        position: absolute;
        top: 0;
    }
    .purple_button{
        width: 100%;
        padding: 0.4em 0.7rem;
    }

    .swichToLoginPage_container{
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    #swichToLoginPage{
        background: transparent;
        border: none;
        color: #80008082;
        font-size: 15px;
        cursor: pointer;
    }

    .form_wrapper{
        width: 43%;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: .5rem;
        border-radius: 4px;
        box-shadow: 2px 2px 8px rgba(0,0,0,0.2);
    }

    .wrap_input_container {
        height: 4.5rem;
        margin-bottom: .15rem;
    }

    input{
        text-transform: capitalize;
        height: 1.7rem;
    }

    label {
        font-size: 12px;
    }

    .email_input, .username_input{
        text-transform: none;
    }

    .logo_wrapper{
        display: flex;
        justify-content: center;
        align-items: center;
        height: 4rem;
    }

    /* LARGE TABLETS */
    @media (max-width: 1024px) {
        .form_wrapper{
            width: 80%;
        }
    }

    /* TABLETS */
    @media (max-width: 768px) {
        .form_wrapper{
            width: 80%;
        }
    }

    /* MOBILE */
    @media (max-width: 414px) {
        .form_wrapper{
            width: 100%;
            padding: 1rem;
            position: absolute;
            top: 0;
            left: 0;
            transform: none;
            background: white;
        }

        .logo_wrapper {
            margin-bottom: 1.5rem;
        }
        .logo_wrapper img {
            width: 8rem;
        }

        .page_wrapper{
            display: block;
            font-size: 13px;
        }

        .row{
            display: block;
        }
    }


</style>

<div class="background"></div>

<div class="form_wrapper">

    <div class="logo_wrapper">
        <img src="./assets/images/mindit_logo.svg" id="mindit_logo" alt="mindit_logo"/>
    </div>

    <div class="motto">Get into the quiz arena!</div>
	
	<div class='page_wrapper'>
		<div class='row'>
			<div class='column'>
				<div class="wrap_input_container">
					<div class="inputElement">
						<label for="text">
							First name
						</label>
						<input type="text" bind:value={userData.firstName} placeholder="First name" on:input={() => setFirstTouched('firstName')} />
					</div>
					{#if (!(validateInput(userData.firstName, 'firstName')) && firstNameWasTouched) || (!(validateInput(userData.firstName, 'firstName')) && triedWithEmpty) }
						<div class="error">Only letters and more than 2 characters!</div>
					{/if}
				</div>
				
				
			</div>
			<div class='column'>
            	<div class="wrap_input_container">
					<div class="inputElement">
						<label for="text">
							Last name
						</label>
						<input type="text" bind:value={userData.lastName} placeholder="Last name" on:input={() => setFirstTouched('lastName')} />
					</div>
					{#if (!(validateInput(userData.lastName, 'lastName')) && lastNameWasTouched) || (!(validateInput(userData.lastName, 'lastName')) && triedWithEmpty) }
						<div class="error">Only letters and more than 2 characters!</div>
					{/if}
				</div>
				
			</div>
		</div>
	</div>

    <div class="page_wrapper">
        <div class="row">
            <div class="column">
                <div class="wrap_input_container">
                    <div class="inputElement">
                        <label for="text">
                            Email
                        </label>
                        <input class="email_input" type="text" bind:value={userData.email} placeholder="Email" on:input={() => setFirstTouched('email')} />
                    </div>
                    {#if (!(validateInput(userData.email, 'email')) && emailWasTouched) || (!(validateInput(userData.email, 'email')) && triedWithEmpty) }
                        <div class="error">Your email is not valid!</div>
                    {/if}
                </div>
            </div>
        </div>
    </div>

    <div class="page_wrapper">
        <div class="row">
            <div class="column">
                <div class="wrap_input_container">
					<div class="inputElement">
						<label for="text">
							Username
						</label>
						<input class="username_input" type="text" bind:value={userData.username} placeholder="Username" on:input={() => setFirstTouched('username')} />
					</div>
					{#if (!(validateInput(userData.username, 'username')) && usernameWasTouched) || (!(validateInput(userData.username, 'username')) && triedWithEmpty) }
						<div class="error">More than 5 characters!</div>
					{/if}
				</div>
            </div>

            <div class="column">
                <div class="wrap_input_container">
					<div class="inputElement">
						<label for="text">
							Password
						</label>
						<input type="password" bind:value={userData.password} placeholder="Password" on:input={() => setFirstTouched('password')} />
					</div>
					{#if (!(validateInput(userData.password, 'password')) && passwordWasTouched) || (!(validateInput(userData.password, 'password')) && triedWithEmpty) }
						<div class="error">More than 5 characters!</div>
					{/if}
				</div>
            </div>
        </div>
    </div>

    <div class="page_wrapper">
        <div class="row">
            <div class="column">
                <div class="wrap_input_container">
                    <div class="inputElement">
                        <label for="text">
                            Card number
                        </label>
                        <input maxlength="19" type="text" bind:value={userData.cardNumber} placeholder="Card number" on:input={() => setFirstTouched('cardNumber')} />
                    </div>
                    {#if (!(validateInput(userData.cardNumber, 'cardNumber')) && cardNumberWasTouched) || (!(validateInput(userData.cardNumber, 'cardNumber')) && triedWithEmpty) }
                        <div class="error">Card number needs 16 numbers!</div>
                    {/if}
                </div>
            </div>
        </div>
    </div>

       <div class="page_wrapper">
        <div class="row">
            <div class="column">
                <div class="wrap_input_container">
					<div class="inputElement">
						<label for="text">
							Expiration Date
						</label>
						<input type="text" bind:value={userData.expDate} placeholder="ex. 01/2019" on:input={() => setFirstTouched('expDate')} />
					</div>
					{#if (!(validateInput(userData.expDate, 'expDate')) && expDateWasTouched) || (!(validateInput(userData.expDate, 'expDate')) && triedWithEmpty) }
						<div class="error">MM/YYYY</div>
					{/if}
				</div>
            </div>

            <div class="column">
                <div class="wrap_input_container">
					<div class="inputElement">
						<label for="text">
							CVV
						</label>
						<input type="CVV" bind:value={userData.CVV} placeholder="CVV" on:input={() => setFirstTouched('CVV')} />
					</div>
					{#if (!(validateInput(userData.CVV, 'CVV')) && CVVWasTouched) || (!(validateInput(userData.CVV, 'CVV')) && triedWithEmpty) }
						<div class="error">CVV needs 3 numbers!</div>
					{/if}
				</div>
            </div>
        </div>
    </div>

    <div class="wrap_input_container">
        <div class="wrap_buttons">
            <button class="purple_button" on:click={signup}>Signup</button>
        </div>
    </div>

    <div class="swichToLoginPage_container">
        <button id="swichToLoginPage" on:click={swichToLoginPage}>Switch to login page</button>
    </div>  

</div>


