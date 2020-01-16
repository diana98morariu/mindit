<script>
	import { onMount } from 'svelte';
	import { curRoute } from '../routing/router.js';


	onMount(() => {
		localStorage.clear();
		curRoute.set('/login');
		window.history.pushState({path: '/login'}, '', window.location.origin + '/login');
		// window.location.href = '/login';
	});
	
</script>
