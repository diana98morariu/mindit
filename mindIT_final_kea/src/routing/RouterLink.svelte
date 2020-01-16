<script>
import { curRoute } from './router.js';
export let page = {
    path: '/home',
    name: 'Home'
}
function redirectTo(event){
  // change current router path
  curRoute.set(event.target.pathname);
  // push the path into web browser history API
  window.history.pushState({path: page.path}, '', window.location.origin + page.path);
}
</script>

<style>
  a {
    display: flex;
    justify-content: center;
    align-items: center;
    text-decoration: none;
    color: #9345D8;
    padding: 1rem 0;
    width: 100%;
  }
</style>

<a href={page.path} on:click|preventDefault={redirectTo}>{page.name}</a>