<script>
    import jq from "jquery";
	import toastr from "toastr";
    import { curRoute } from '../routing/router.js';
    import { onMount } from 'svelte';

	onMount(() => {
        jq('#pageContent').addClass('authPage');
		jq('.header_container').addClass('notShow');
		jq('.retrieve_payments').addClass('notShow');
	})

	const basicURL = '../../../backend/mindIT_backend/apis/';

    let user_emailOrUsername = 'admin_mindit', user_password = 'administrator';
    
    let smthWasTouched = false,  passwordWasTouched = false, usernameWasTouched = false, triedWithEmpty = false;
    
    const login = () => {
        jq.ajax({
            type: "POST",
            url: basicURL + "api-login.php",
            dataType: "json",
            data: {
                user_emailOrUsername: user_emailOrUsername,
                user_password: user_password
            },
            success: function(data) {
                // console.log(data);
                if(data.status === 1){
                    localStorage.token = data['token'];
                    localStorage.userName = data['userName'];
                    // window.location.href = '/home';
                    curRoute.set('/home');
                    window.history.pushState({path: '/home'}, '', window.location.origin + '/home');
                } else if(data.message === 'incorrect credentials'){
                    toastr.error('Incorrect credentials. Please try again!');
                } else if(data.message === 'your account is not active anymore'){
                    toastr.error('Your account was deactivated. Please contact us to use it again!');
                }
            },
            error: function() {
                // console.log("Error: Login Failed");
            }
        });
    };
    
    toastr.options = {
		"positionClass": "toast-bottom-right",
		"preventDuplicates": true,
    }

    const swichToSignupPage = () => {
        curRoute.set('/signup');
        window.history.pushState({path: '/signup'}, '', window.location.origin + '/signup');
    }
    
    const setFirstTouched = (input) => {
		smthWasTouched = true;
		if(input === 'username') {usernameWasTouched = true};
		if(input === 'password') {passwordWasTouched = true};
	}

	const validateInput = (elmValue, input) => {
		let isValid = false;
		triedWithEmpty = false;
		switch(input) {
			case 'username':
				isValid = elmValue.replace(/ /g,'').length > 5;
				break;
			case 'password':
				isValid = elmValue.length > 5;
                break;
			default:
				// console.log(`VALIDATION FAILED: no validation for: ${input}`);
				break;
		}
		return isValid;
	}

	const areThereErrors = () => { return jq('.error').length > 0 ? true : false }

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
    
    .swichToSignupPage_container{
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    #swichToSignupPage{
        background: transparent;
        border: none;
        color: black;
        font-size: 15px;
    }

    #swichToSignupPage span{
        cursor: pointer;
        color: #80008082;
    }

    .wrap_buttons{
        margin-top: 0;
    }

    .form_wrapper{
        width: 35%;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 3rem 2rem;
        border-radius: 4px;
        box-shadow: 2px 2px 8px rgba(0,0,0,0.2);
    }

    .wrap_input_container {
        height: 90px;
    }

    .logo_wrapper{
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: .19rem;
    }

    /* LARGE TABLETS */
    @media (max-width: 1024px) {
        .form_wrapper{
            width: 65%;
        }
    }

    /* TABLETS */
    @media (max-width: 768px) {
        .form_wrapper{
            width: 80%;
        }
    }

    /* LARGE MOBILES */
    @media (max-width: 414px) {
        .form_wrapper{
            width: 90%;
        }
    }

    /* SMALL MOBILES */
    @media (max-width: 414px) {
        .form_wrapper{
            width: 95%;
        }

        .logo_wrapper img {
            width: 70%;
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
							Email or username
						</label>
						<input type="text" bind:value={user_emailOrUsername} placeholder="Email or username" on:input={() => setFirstTouched('username')} />
					</div>
					{#if (!(validateInput(user_emailOrUsername, 'username')) && usernameWasTouched) || (!(validateInput(user_emailOrUsername, 'username')) && triedWithEmpty) }
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
							Password
						</label>
						<input type="password" bind:value={user_password} placeholder="Password" on:input={() => setFirstTouched('password')} />
					</div>
					{#if (!(validateInput(user_password, 'password')) && passwordWasTouched) || (!(validateInput(user_password, 'password')) && triedWithEmpty) }
						<div class="error">More than 5 characters!</div>
					{/if}
				</div>
            </div>
        </div>
    </div>

    <div class="wrap_input_container">
        <div class="wrap_buttons">
            <button class="purple_button" on:click={login}>Login</button>
        </div>
    </div>

    <div class="swichToSignupPage_container">
        <button id="swichToSignupPage">Don't have an account yet? <span on:click={swichToSignupPage}> Sign up now</span></button>
    </div>  


</div>



