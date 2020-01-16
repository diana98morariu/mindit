<script>
	import toastr from "toastr";
	import jq from "jquery";
	import { curRoute } from '../routing/router.js';

	const basicURL = '../../../backend/mindIT_backend/apis/';
	let arrayOfQuestions = [];

	toastr.options = {
		"positionClass": "toast-bottom-right",
		"preventDuplicates": true,
    }

	let nameWasTouched = false, answerWasTouched = false, diffWasTouched = false, questionWasTouched = false, triedWithEmpty = false;
	let quizName = '', questionValue = '', questionAnswer = '', questionDifficulty = 0, canCreateQuiz = false;

	const difficultyLevel = [
		{ value: 0, text: 'Select difficulty level' },
		{ value: 1, text: 'Easy' },
		{ value: 2, text: 'Medium' },
		{ value: 3, text: 'Hard' }
	];

	const addQuestion = () => {
		if (questionDifficulty !== 0 && questionValue.length > 1 && questionAnswer.length > 1) {
			const newQuestion = {
				questionContentValue: questionValue,
				questionAnswerValue: questionAnswer,
				questionDifficultyValue: questionDifficulty,
			};
			arrayOfQuestions.push(newQuestion);
			questionValue = '', questionAnswer = '', questionDifficulty = 0, answerWasTouched = false, diffWasTouched = false, questionWasTouched = false;
			if(arrayOfQuestions.length > 1){
				canCreateQuiz = true;
				toastr.success('Your question has been added. You can create your quiz now or you can add more questions');
			} else {
				toastr.success(`Your question has been added. Add at least ${arrayOfQuestions.length} more questions to create the quiz`);
			}
		} else {
			triedWithEmpty = true;
		}
	}

    const createQuiz = () => {
		if(quizName.length > 0 && canCreateQuiz) {
			jq.ajax({
				type: "POST",
				url: basicURL + "api-create-quiz.php",
				dataType: "json",
				data: {
					name: quizName,
					questions: JSON.stringify(arrayOfQuestions),
					token: localStorage.token
				},
				success: (data) => {
					toastr.success('Your quiz has been registered successfully');
					curRoute.set('/home');
					window.history.pushState({path: '/home'}, '', window.location.origin + '/home');
				},
				error: () => {
					alert("Error: Login Failed");
				}
			});
		}
	};
	
	const showError = (value) => {
		if( value.length < 2 ) {
			return true;
		} 
		return false;
	}

	const validateInput = (value, input) => {
		triedWithEmpty = false;
		if(input === 'name') {nameWasTouched = true};
		if(input === 'question') {questionWasTouched = true};
		if(input === 'answer') {answerWasTouched = true};
		if(input === 'diff') {diffWasTouched = true};
		if( value.length > 1 || (Number.isInteger(value) && value > 0) ) {
			return true;
		} 
		return false;
	}

</script>
<style>
  	.form_container {
		width: 100%;
		padding: .5rem .2rem;
	}
	#filter_container{
		align-items: flex-end;
	}

	#filter_container select{
		width: 195px;
	}

	.add_question_button{
		color: #333;
		outline: none;
		border: none;
		font-size: 20px;
		background: transparent;
		padding: 0;
		cursor: pointer;
		padding: 5px 10px;
	}

	.add_question_button:hover{
		background: #9E5BD8;
		color: #fff;
	}

	.purple_button{
		margin-top: 1.7rem;
		width: 100%;
		width: 24.5%;
    	margin-left: .2rem;
	}

	.wrap_input_container{
		margin-bottom: 1.15rem;
	}
</style>

<h1>Create a new quiz</h1>

<div class="page_content">
	<div class="form_container">
		<div class="wrap_input_container">
			<div class="inputElement">
				<label for="text">
					Quiz name
				</label>
				<input type="text" bind:value={quizName} placeholder="Enter your quiz name here" on:input={() => validateInput(quizName, 'name')} />
			</div>
			{#if (showError(quizName) && nameWasTouched) || (showError(quizName) && triedWithEmpty) }
				<div class="error">More than one character!</div>
			{/if}
		</div>
		<div class="wrap_input_container">
			<div class="inputElement">
				<label for="text">
					Question
				</label>
				<textarea type="text" bind:value={questionValue} placeholder="Enter your question here" on:input={() => validateInput(questionValue, 'question')}></textarea>
			</div>
			{#if (showError(questionValue) && questionWasTouched) || (showError(questionValue) && triedWithEmpty)}
				<div class="error">More than one character!</div>
			{/if}
		</div>

		<div class="wrap_input_container">
			<div class="inputElement">
				<label for="lastName">
					Correct answer
				</label>
					<textarea id="lastName" bind:value={questionAnswer} name="questionAnswer" placeholder="Enter the correct answer here" on:input={() => validateInput(questionAnswer, 'answer')}></textarea>
			</div>
			{#if (showError(questionAnswer) && answerWasTouched) || (showError(questionAnswer) && triedWithEmpty) }
				<div class="error">More than one character!</div>
			{/if}
		</div>

		<div id="filter_container">
			<div class="container_left_side">
				<label for="questionDifficulty">Choose question difficulty</label>
				<select bind:value={questionDifficulty} name="questionDifficulty" on:change={() => validateInput(questionDifficulty, 'diff')}>
					{#each difficultyLevel as level}
						<option value={level.value}>
							{level.text}
						</option>
					{/each}
				</select>
			</div>
			<button on:click={ addQuestion } class="add_question_button">╋ &nbsp;  Add question</button>
		</div>

		{#if (diffWasTouched && questionDifficulty === 0) || (triedWithEmpty && questionDifficulty === 0) }
			<div class="error">Please select difficulty</div>
		{/if}

	</div>
	{#if canCreateQuiz}
		<button on:click={ createQuiz } class="purple_button">Create quiz</button>
	{/if}
</div>

