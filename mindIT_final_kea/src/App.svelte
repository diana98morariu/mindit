<script>
	import Header from './components/Header.svelte';
	import router, { curRoute } from './routing/router.js';
	import { onMount } from 'svelte';
	import jq from "jquery";
	import toastr from "toastr";

	 toastr.options = {
		"positionClass": "toast-bottom-left",
		"preventDuplicates": true,
    }

	let showHeader = true, isAdmin = false, isAuthPage = false;

	onMount(async () => {
		await promiseCheckUser;
		if(window.location.pathname === '/login' || window.location.pathname === '/signup') {
			isAuthPage = true;
		}
		if(!allowUser) {
			if(window.location.pathname === '/login' || window.location.pathname === '/signup'){
				curRoute.set(window.location.pathname);
				window.history.pushState({path: window.location.pathname}, '', window.location.origin + window.location.pathname);
				showHeader = false;
			} else {
				curRoute.set('/login');
				window.history.pushState({path: '/login'}, '', window.location.origin + '/login');
				showHeader = false;
			}
		} else {
			curRoute.set('/home');
			window.history.pushState({path: '/home'}, '', window.location.origin + '/home');
			showHeader = true;
		}
		if (!history.state) {
			window.history.replaceState({path: window.location.pathname}, '',   window.location.href)
		}
	})

	const basicURL = '../../backend/mindIT_backend/apis/';

	const isUserLogged = async () => {
		const response = await jq.ajax({
			type: 'GET',
			url: basicURL + "app_client.php",
			dataType: "json",
			data: {
				token: localStorage.token
			},
			success: (data) => {
				if (typeof data['userID'] !== 'undefined') {
					const userString = 'administrator040e2b167b33b';
					isAdmin = data['userID'].indexOf(userString) !== -1 ? true : false;
					var alertMessage = 'You have a valid token! Here is your user Id: ' + data['userID'];
					// console.log(alertMessage);
					allowUser = true;
				} 
				else if (typeof data['error'] !== 'undefined') {
					// console.log('Error: ' + data['error']);
				}
				else {
					// console.log('Error: Your request has failed.');
				}
			},
			error: error => {
				// console.log(error);
			}
        });
	}

	const retrievePayments = () => {
		jq.ajax({
			type: 'GET',
			url: basicURL + "api-create-payment-procedure.php",
			dataType: "json",
			data: {
				token: localStorage.token
			},
			success: (data) => {
				// console.log(data);
				if( data.status === 1 ){
					toastr.success(`All payments retrieved successfully! Deleted users: ${data.deletedUsers}`);
				}
			},
			error: error => {
				// console.log(error);
			}
        });
	}

	let allowUser = false;
	const promiseCheckUser = isUserLogged();
	const sourceImage = './assets/images/test.png';

</script>

<!-- ****************************************************** -->
<!-- ************************Styles************************ -->
<!-- ****************************************************** -->

<style>
	#pageContent{
		width: 100%;
		padding: 0 18%;
		margin-top: 6rem;
	}

	.authPage{
		margin-top: 0 !important;
		padding: 0 !important;
	}

	.notShow{
		display: none;
	}

	.retrieve_payments{
		position: fixed;
		bottom: 1.5rem;
		right: 1.5rem;
		background: #E57E39;
		color: white;
		border: none;
		cursor: pointer;
	}

	.retrieve_payments:hover{
		opacity: .69;
	}
	
</style>
<!-- ****************************************************** -->
<!-- ************************HTML************************** -->
<!-- ****************************************************** -->


	{#if isAdmin}
		<button class="retrieve_payments" on:click={retrievePayments}>Retrieve payments</button>
	{/if}

	<div class:notShow="{isAuthPage === true}">
		<Header></Header>
	</div>

	<div id="pageContent" class:authPage="{isAuthPage === true}">
		<!-- Page component updates here -->
		<svelte:component this={router[$curRoute]} />
	</div>












