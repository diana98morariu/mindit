
<script>
	import RouterLink from '../routing/RouterLink.svelte';
	import { curRoute } from '../routing/router.js';

	const basicURL = '../../../backend/mindIT_backend/apis/';	

	function handlerBackNavigation(event){
		curRoute.set(event.state.path)
	}

	function toHomePage(event){
		curRoute.set('/home');
		window.history.pushState({path: '/home'}, '', window.location.origin + '/home');
	}
	
</script>

<style>
	.header_container {
		width: 100%;
		height: 74px;
		border-radius: 2px;
		/* box-shadow: 1px 1px 2px rgba(0,0,0,0.1); */
		padding: 0.75rem;
		display: flex;
		justify-content: space-between;
		align-items: center;
		position: fixed;
		top: 0;
		z-index: 100;
    	background: white;
	}

	#profile_container{
		display: flex;
		justify-content: space-between;
		align-items: center;
		cursor: pointer;
		margin-right: 1.5rem;
		margin-top: 23px;
	}

	#profile_name{
		margin-bottom: 7px;
		font-size: 15px;
		text-transform: capitalize;
	}


	#mindit_user{
		width: 74%;
	}

	#profile_name {
		color: #9345D8;
	}

	.dropdown {
		position: relative;
		display: inline-block;
	}

	.dropdown-content {
		top: 36px;
		display: none;
		position: absolute;
		background-color: #f9f9f9;
		width: 7.5rem;
		box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
		z-index: 1;
	}

	.dropdown-content .link {
		display: flex;
		justify-content: flex-end;
		align-items: center;
		text-decoration: none;
		color: #9345D8;
		font-size: 14px;
	}

	.dropdown-content .link:hover {background-color: #f1f1f1}

	.dropdown:hover .dropdown-content {
		display: block;
	}

	/* LARGE TABLETS */
	@media (max-width: 1024px) {

	}

	/* TABLETS */
	@media (max-width: 768px) {

	}

	/* MOBILE */
	@media (max-width: 414px) {
		#profile_container{
			margin-right: 0;
			
		}

		.dropdown-content{
			right: -1rem;
		}

	}



</style>
<svelte:window on:popstate={handlerBackNavigation} />

<div class="header_container">
	<img on:click={toHomePage} src="./assets/images/mindit_logo.svg" id="mindit_logo" alt="mindit_logo"/>

	<div id="profile_container" class="dropdown">
		<div id="profile_icon"><img src="./assets/images/user_default.svg" id="mindit_user" alt="mindit_logo"/></div>
		<div id="profile_name">
			{localStorage.userName}
		</div>
		<div class="dropdown-content">
			<div class="link"> <RouterLink  page={{path: '/my-profile', name: 'My profile'}} /> </div>
			<div class="link"> <RouterLink  page={{path: '/my-quizzes', name: 'My quizzes'}} /> </div>
			<div class="link"> <RouterLink  page={{path: '/logout', name: 'Logout'}} /> </div>
			<!-- <RouterLink page={{path: '/login', name: 'Login'}} /> -->
		</div>
	</div>
</div>
