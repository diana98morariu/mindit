<script>
	import jq from "jquery";
	import toastr from "toastr";
    import { curRoute } from '../routing/router.js';
	import { onMount } from 'svelte';
	
	onMount(() => {
		if(localStorage.getItem('token') === null){
			curRoute.set('/login');
			window.history.pushState({path: '/login'}, '', window.location.origin + '/login');
			// location.reload();
		}
	})

	const basicURL = '../../../backend/mindIT_backend/apis/';
	let userData = {};

	let smthWasTouched = false, firstNameWasTouched = false, lastNameWasTouched = false, usernameWasTouched = false, passwordWasTouched = false, addressWasTouched = false,
		postalCodeWasTouched = false, phoneWasTouched = false, cityWasTouched = false, triedWithEmpty = false;

    toastr.options = {
		"positionClass": "toast-bottom-right",
		"preventDuplicates": true,
	}
	


	const getUserDetails = async () => {
		const user = await jq.ajax({
            type: 'GET',
			url: basicURL + 'api-get-user-details.php',
			dataType: "json",
			data: {
				token: localStorage.token
			},
			success: (data) => {
				// console.log(data)
				userData = data;
				const primaryCardArray = userData.creditCards.filter((card) => { return card.isPrimary === 1} );
				userData.primaryCard = primaryCardArray[0];
				userData.primaryCard.number = userData.primaryCard.number.replace(/(\d{4})/g, '$1 ').replace(/(^\s+|\s+$)/,'');
				userData.primaryCard.number = userData.primaryCard.number.replace(/^.{14}/g, '**** **** ****');
				delete userData.creditCards;
				if(userData.phone === null){userData.phone = ''};
				if(userData.postalCode === null){userData.postalCode = ''};
				if(userData.address === null){userData.address = ''};
				if(userData.city === null){userData.city = ''};
				// console.log(userData);
                return userData;
			},
			error: error => {
				// console.log(error);
			}
		});
		if (user) {
			return user;
		} else {
            throw new Error();
		}
	}

	let promiseUser = getUserDetails();
	
	const setFirstTouched = (input) => {
		smthWasTouched = true;
		if(input === 'firstName') {firstNameWasTouched = true};
		if(input === 'lastName') {lastNameWasTouched = true};
		if(input === 'username') {usernameWasTouched = true};
		if(input === 'password') {passwordWasTouched = true};
		if(input === 'address') {addressWasTouched = true};
		if(input === 'postalCode') {postalCodeWasTouched = true};
		if(input === 'phone') {phoneWasTouched = true};
		if(input === 'city') {cityWasTouched = true};
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
			case 'address':
				isValid = elmValue.replace(/ /g,'').length > 5;
				break;
			case 'postalCode':
				isValid = (elmValue.replace(/ /g,'').length === 4 && /^\d+$/.test(elmValue.replace(/ /g,''))) ? true : false;
				break;
			case 'phone':
				isValid = elmValue.replace(/ /g,'').length >= 8 && elmValue.replace(/ /g,'').length < 12;
				break;
			case 'city':
				isValid = elmValue.replace(/ /g,'').length > 2;
				break;
			default:
				// console.log(`VALIDATION FAILED: no validation for: ${input}`);
				break;
		}
		return isValid;
	}

	const toEditCards = () => {
		curRoute.set('/edit-cards');
		window.history.pushState({path: '/edit-cards'}, '', window.location.origin + '/edit-cards');
	}

	const areThereErrors = () => { return jq('.error').length > 0 ? true : false }

	const editProfile = () => {
        if(areThereErrors()) {
            toastr.error('Please make sure all the fields are completed and valid!');
            return;
		}

		//REMOVE EMPTY KEYS
		const userDataForRequest = Object.assign({}, userData);
		Object.keys(userDataForRequest).forEach((key) => (userDataForRequest[key] == '') && delete userDataForRequest[key]);

		if(smthWasTouched) {
			jq.ajax({
				type: "POST",
				url: basicURL + "api-edit-user-profile.php",
				dataType: "json",
				data: {
                    user_firstName: userDataForRequest.firstName,
                    user_lastName: userDataForRequest.lastName,
                    user_username: userDataForRequest.username,
                    user_password: userDataForRequest.password,
                    user_address: userDataForRequest.address,
                    user_phoneNumber: userDataForRequest.phone,
                    user_postalCode: userDataForRequest.postalCode,
                    user_city: userDataForRequest.city,
					token: localStorage.token
				},
				success: (data) => {
					// console.log(data);
					toastr.success('Your user has been edited successfully');
					// curRoute.set('/my-quizzes');
					// window.history.pushState({path: '/my-quizzes'}, '', window.location.origin + '/my-quizzes');
				},
				error: (err) => {
					// console.log(err);
				}
			});
		} else {
			toastr.info('No changes were made! Please make a change in order to edit the user');
		}
	};
	
	const deleteProfile = () => {
		jq.ajax({
			type: "GET",
			url: basicURL + "api-delete-profile.php",
			dataType: "json",
			data: {
				token: localStorage.token
			},
			success: (data) => {
				// console.log(data);
				if(data.status === 1) {
					localStorage.clear()
					toastr.success('Your user has been deleted successfully');
					curRoute.set('/signup');
					window.history.pushState({path: '/signup'}, '', window.location.origin + '/signup');
				}
			},
			error: (err) => {
				// console.log(err);
			}
		});
	}

</script>

<style>
	.edit_card_button{
		background: transparent;
		border: 1px solid #9E5BD8;
		color: #9E5BD8;
	}

	.edit_card_button:hover{
		background: #9E5BD8;
		color: white;
	}

	.purple_button, .orange_button{
		width: 100%;
		padding: 0.4em 0.7rem;
		margin-top: -15px;
	}

</style>

{#await promiseUser}
    <div class="loader">Loading...</div>
{:then user}
	<div class="page_title">Your profile</div>
	
	<div class='page_wrapper'>
		<div class='row'>
			<div class='column'>
				<div class="wrap_input_container">
					<div class="inputElement">
						<label for="text">
							First name
						</label>
						<input type="text" bind:value={user.firstName} placeholder="First name" on:input={() => setFirstTouched('firstName')} />
					</div>
					{#if (!(validateInput(user.firstName, 'firstName')) && firstNameWasTouched) || (!(validateInput(user.firstName, 'firstName')) && triedWithEmpty) }
						<div class="error">Only letters and more than 2 characters!</div>
					{/if}
				</div>
				<div class="wrap_input_container">
					<div class="inputElement">
						<label for="text">
							Last name
						</label>
						<input type="text" bind:value={user.lastName} placeholder="Last name" on:input={() => setFirstTouched('lastName')} />
					</div>
					{#if (!(validateInput(user.lastName, 'lastName')) && lastNameWasTouched) || (!(validateInput(user.lastName, 'lastName')) && triedWithEmpty) }
						<div class="error">Only letters and more than 2 characters!</div>
					{/if}
				</div>
				<div class="wrap_input_container">
					<div class="inputElement">
						<label for="text">
							Username
						</label>
						<input type="text" bind:value={user.username} placeholder="Username" on:input={() => setFirstTouched('username')} />
					</div>
					{#if (!(validateInput(user.username, 'username')) && usernameWasTouched) || (!(validateInput(user.username, 'username')) && triedWithEmpty) }
						<div class="error">More than 5 characters!</div>
					{/if}
				</div>
				<div class="wrap_input_container">
					<div class="inputElement">
						<label for="text">
							Password
						</label>
						<input type="password" bind:value={user.password} placeholder="Password" on:input={() => setFirstTouched('password')} />
					</div>
					{#if (!(validateInput(user.password, 'password')) && passwordWasTouched) || (!(validateInput(user.password, 'password')) && triedWithEmpty) }
						<div class="error">More than 5 characters!</div>
					{/if}
				</div>
			</div>
			<div class='column'>
				<div class="wrap_input_container">
					<div class="inputElement">
						<label for="text">
							Address (Street, number)
						</label>
						<input type="text" bind:value={user.address} placeholder="Address" on:input={() => setFirstTouched('address')} />
					</div>
					{#if (!(validateInput(user.address, 'address')) && addressWasTouched) || (!(validateInput(user.address, 'address')) && triedWithEmpty) }
						<div class="error">More than 5 characters!</div>
					{/if}
				</div>
				<div class="wrap_input_container">
					<div class="inputElement">
						<label for="text">
							Postal code
						</label>
						<input type="text" bind:value={user.postalCode} placeholder="Postal code" on:input={() => setFirstTouched('postalCode')} />
					</div>
					{#if (!(validateInput(user.postalCode, 'postalCode')) && postalCodeWasTouched) || (!(validateInput(user.postalCode, 'postalCode')) && triedWithEmpty) }
                        <div class="error">Postal code needs 4 numbers!</div>
					{/if}
				</div>
				<div class="wrap_input_container">
					<div class="inputElement">
						<label for="text">
							Primary card
						</label>
						<input class="readonly_input" type="text" bind:value={user.primaryCard.number} readonly />
					</div>
				</div>
				<div class="wrap_input_container">
					<div class="wrap_buttons">
						<button class="purple_button" on:click={editProfile}>Edit profile</button>
					</div>
				</div>
			</div>
			<div class='column'>
				<div class="wrap_input_container">
					<div class="inputElement">
						<label for="text">
							Phone number
						</label>
						<input type="text" bind:value={user.phone} placeholder="Phone number" on:input={() => setFirstTouched('phone')} />
					</div>
					{#if (!(validateInput(user.phone, 'phone')) && phoneWasTouched) || (!(validateInput(user.phone, 'phone')) && triedWithEmpty) }
                        <div class="error">Phone number needs 8 numbers!</div>
					{/if}
				</div>
				<div class="wrap_input_container">
					<div class="inputElement">
						<label for="text">
							City
						</label>
						<input type="text" bind:value={user.city} placeholder="City" on:input={() => setFirstTouched('city')} />
					</div>
					{#if (!(validateInput(user.city, 'city')) && cityWasTouched) || (!(validateInput(user.city, 'city')) && triedWithEmpty) }
						<div class="error">More than 2 characters!</div>
					{/if}
				</div>
				<div class="wrap_input_container">
					<div class="wrap_buttons">
						<button class="purple_button edit_card_button" on:click={toEditCards}>Edit cards</button>
					</div>
				</div>
				<div class="wrap_input_container">
					<div class="wrap_buttons">
						<button class="orange_button" on:click={deleteProfile}>Delete profile</button>
					</div>
				</div>
			</div>
		</div>
	</div>

{:catch error}
	<p style="color: red">{error.message}</p>
{/await}


