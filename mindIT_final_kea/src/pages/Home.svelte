<script>
	import jq from "jquery";
	import Quiz from '../components/Quiz.svelte';
	import { curRoute } from '../routing/router.js';
	import { onMount } from 'svelte';

	onMount(() => {
		jq('div').removeClass('authPage');
		jq('div').removeClass('notShow');
		jq('#profile_name').text(localStorage.userName);
	})

	const basicURL = '../../../backend/mindIT_backend/apis/';

	let filterOptions = [
		{ id: 0, text: `All difficulties`, value: 'unset' },
		{ id: 1, text: `Very Easy`, value:'VeryEasy' },
		{ id: 2, text: `Easy`, value:'Easy' },
		{ id: 3, text: `Medium`, value:'Medium'},
		{ id: 4, text: `Hard`, value:'Hard'},
		{ id: 5, text: `Very Hard`, value:'VeryHard' }
	];

	let selected;
	let search = '';
	
	const handleSearch = async () => {
		let matchesQuizzes = [];
		const searchedQuizzes = await jq.ajax({
			type: 'GET',
			url: basicURL + 'api-search-and-filter.php',
			data: {
				search: search,
				limit: 20,
				token: localStorage.token,
				filter: selected.value
			},
			dataType: "json",
			success: (matches) => {
				matchesQuizzes = matches;
			},
			error: error => {
				// console.log(error);
			}
		});
		promiseQuizzes = matchesQuizzes;
	}

	const getInitialData = async () => {
		const limit = 20;
		const quizzesArray = await jq.ajax({
			type: 'GET',
			url: basicURL + `api-get-quizzes.php?limit=${limit}`,
			dataType: "json",
			data: {
				token: localStorage.token
			},
			success: (data) => {
				// console.log(data);
				return data;
			},
			error: error => {
				// console.log(error);
			}
		});
		if (quizzesArray) {
			return quizzesArray;
		} else {
			throw new Error();
		}
	}

	let promiseQuizzes = getInitialData();

	function toCreateQuizPage(){
		curRoute.set('/create-quiz');
		window.history.pushState({path: '/create-quiz'}, '', window.location.origin + '/create-quiz');
	}

	function toQuizPage(quiz_id){
		curRoute.set('/quiz');
		const quizPage = '/quiz?id=' + quiz_id;
		window.history.pushState({path: '/quiz'}, '', window.location.origin + quizPage);
	}

</script>

<style>
/* LARGE TABLETS */
	@media (max-width: 1024px) {

	}

	/* TABLETS */
	@media (max-width: 768px) {

	}

	/* MOBILE */
	@media (max-width: 414px) {
		#searchBar_container{
			top: 3rem;
			z-index: 1;
		}
		#searchBar_container #searchBar{
			display: none;
		}
	}
</style>

{#await promiseQuizzes}
    <div class="loader">Loading...</div>
{:then quizzes }
	<div id="searchBar_container">
		<div id="searchBar">
			<input id="search_input" type="text" placeholder="Search for a quiz" name="search" maxlength="30" on:input={handleSearch} bind:value={search}/>
		</div>
		<div id="top_bar">
			<div id="filter_container">
				<img src="./assets/images/filter_icon.svg" id="filter_icon" alt="filter_icon"/>
				<select bind:value={selected} on:change={handleSearch}>
					{#each filterOptions as difficulty}
						<option value={difficulty}>
							{difficulty.text}
						</option>
					{/each}
				</select>
			</div>
			<div id="create_quiz">
				<button class="purple_button" on:click={toCreateQuizPage}>+ Create quiz</button>
			</div>
		</div>
	</div>
	{#if quizzes.length > 0}
		<div class="quizzes">
			{#each quizzes as {id, name, questionsAmount, difficulty, user_first_name, user_last_name, user_id}, i} 
				<Quiz id={id}>
					<div class="quiz_top_container">
						<div class="quiz_name">{name}</div>
						<div class="quiz_difficulty">Difficulty: {difficulty}</div>
					</div>
					<div class="quiz_questions_amount">{questionsAmount} Questions</div>
					<div class="quiz_bottom_container">
						<div class="quiz_author">created by {user_first_name} { user_last_name}</div>
						<button class="purple_button" on:click|preventDefault={() => toQuizPage(id)}>Enter quiz</button>
					</div>
				</Quiz>
			{/each}
		</div>
		{:else}
			<div id="no_quizzes">No quizzes were found for your search</div>
	{/if}
{:catch error}
    <p style="color: red">{error.message}</p>
{/await}



