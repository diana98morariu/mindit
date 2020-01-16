<script>
	import jq from "jquery";
	import Quiz from '../components/Quiz.svelte';
	import { curRoute } from '../routing/router.js';

	const basicURL = '../../../backend/mindIT_backend/apis/';
	let myQuizzesData = [];

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
		const searchedQuizzes = await jq.ajax({
			type: 'GET',
			url: basicURL + 'api-search-and-filter.php',
			data: {
				search: search,
				limit: 20,
				token: localStorage.token,
				filter: selected.value,
				myQuizzes: "1"
			},
			dataType: "json",
			success: (matches) => {
				promiseQuizzes = matches;
			},
			error: error => {
				// console.log(error);
			}
		});
	}

	const getInitialData = async () => {
		const limit = 20;
		const quizzesArray = await jq.ajax({
			type: 'GET',
			url: basicURL + `api-get-user-quizzes.php?limit=${limit}`,
			dataType: "json",
			data: {
				token: localStorage.token
			},
			success: (data) => {
				myQuizzesData = data;
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

	function toEditPage(quiz_id){
		curRoute.set('/edit-quiz');
		const quizPage = '/edit-quiz?id=' + quiz_id;
		window.history.pushState({path: '/edit-quiz'}, '', window.location.origin + quizPage);
	}

	function deleteQuiz(quiz_id){
		jq.ajax({
			type: 'GET',
			url: basicURL + 'api-delete-quiz.php',
			dataType: "json",
			data: {
				token: localStorage.token,
				quizID: quiz_id
			},
			success: (data) => {
				// console.log(data);
				myQuizzesData = myQuizzesData.filter(quiz => {return quiz.id !== quiz_id});
				promiseQuizzes = myQuizzesData;

			},
			error: error => {
				// console.log(error);
			}
		});
	}

</script>

<style>
	.purple_button, .orange_button {
		width: 10rem;
	}

	.purple_button{
		margin-right: .5rem;
	}

	 /* LARGE TABLETS */
	@media (max-width: 1024px) {

	}

	/* TABLETS */
	@media (max-width: 768px) {
		.buttons_container{
			width: 10rem;
		}

		.buttons_container .purple_button{
			margin-bottom: 0.5rem;
		}

		.quiz_bottom_container .quiz_author{
			width: 13rem;
		}

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
			{#each quizzes as {id, name, createdAt, questionsAmount, difficulty, user_first_name, user_last_name, user_id}, i} 
				<Quiz id={id}>
					<div class="quiz_top_container">
						<div class="quiz_name">{name}</div>
						<div class="quiz_difficulty">Difficulty: {difficulty}</div>
					</div>
					<div class="quiz_questions_amount">{questionsAmount} Questions</div>
					<div class="quiz_bottom_container">
						<div class="quiz_author">created by {user_first_name} { user_last_name}</div>
						<div class="buttons_container">
							<button class="purple_button" on:click|preventDefault={() => toEditPage(id)}>Edit quiz</button>
							<button class="orange_button" on:click|preventDefault={() => deleteQuiz(id)}>Delete quiz</button>
						</div>
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



