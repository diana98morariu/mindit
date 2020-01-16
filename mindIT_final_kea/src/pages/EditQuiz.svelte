<script>
	import toastr from "toastr";
	import jq from "jquery";
    import { curRoute } from '../routing/router.js';
    import { onMount } from 'svelte';

    const urlParams = new URLSearchParams(window.location.search);
    const quiz_id = urlParams.get('id');

    onMount(() => {
        if(!quiz_id){
            curRoute.set('/my-quizzes');
            window.history.pushState({path: '/my-quizzes'}, '', window.location.origin + '/my-quizzes');
        }
    })

    const basicURL = '../../../backend/mindIT_backend/apis/';

    toastr.options = {
		"positionClass": "toast-bottom-right",
		"preventDuplicates": true,
    }

    let quizData = {}, addNewQuestion = false;

	let nameWasTouched = false, answerWasTouched = false, diffWasTouched = false, questionWasTouched = false, triedWithEmpty = false;
    let questionValue = '', questionAnswer = '', questionDifficulty = "0", canEditQuiz = true;

	const difficultyLevel = [
		{ value: "0", text: 'Select difficulty level' },
		{ value: "1", text: 'Easy' },
		{ value: "2", text: 'Medium' },
		{ value: "3", text: 'Hard' }
    ];
    
    const addQuestion = () => {
        setTimeout(() => {jq('.add_question_button')[0].scrollIntoView({ block: 'end',  behavior: 'smooth' });});
        if(areThereErrors()) {
            toastr.error('Please make sure all the fields are completed!');
            return;
        }
		if (questionDifficulty !== "0" && questionValue.length > 1 && questionAnswer.length > 1) {
			const newQuestion = {
				questionContent: questionValue,
				questionAnswer: questionAnswer,
				questionDifficulty: questionDifficulty,
			};
            quizData.questions.push(newQuestion);
			questionValue = '', questionAnswer = '', questionDifficulty = "0", answerWasTouched = false, diffWasTouched = false, questionWasTouched = false;
			if(quizData.questions.length > 1){
				toastr.success('Your question has been added. You can edit your quiz now or you can add more questions');
			} else {
				toastr.success(`Your question has been added. Add at least ${quizData.questions.length} more questions to create the quiz`);
            }
            promiseQuiz = quizData;
            // console.log(quizData);
		} else {
			triedWithEmpty = true;
		}
    }

    const removeQuestion = (question_id) => {
        // console.log(quizData)
        quizData.questions = quizData.questions.filter(question => {return question.questionID !== question_id});
        if( typeof question_id !== 'undefined') {quizData.removedQuestions.push(question_id);}
        promiseQuiz = quizData;
    }
    
    const editQuiz = () => {
        if(areThereErrors()) {
            toastr.error('Please make sure all the fields are completed!');
            return;
        }
		if(quizData.quizName.length > 1) {
			jq.ajax({
				type: "POST",
				url: basicURL + "api-edit-quiz.php",
				dataType: "json",
				data: {
                    name: quizData.quizName,
                    id: quiz_id,
					questions: JSON.stringify(quizData.questions),
					removedQuestions: JSON.stringify(quizData.removedQuestions),
					token: localStorage.token
				},
				success: (data) => {
                    // console.log(data);
					toastr.success('Your quiz has been edited successfully');
					curRoute.set('/my-quizzes');
					window.history.pushState({path: '/my-quizzes'}, '', window.location.origin + '/my-quizzes');
				},
				error: () => {
					alert("Error: Login Failed");
				}
			});
		}
    };
    
    const areThereErrors = () => { return jq('.error').length > 0 ? true : false }

    const showAddQuestion = () => { 
        addNewQuestion = addNewQuestion === false ? true : false;
        addNewQuestion === true ? jq('#show_add_question_button').text('Hide new question') : jq('#show_add_question_button').text('Add a new question');
        setTimeout(() => {jq('#show_add_question_button')[0].scrollIntoView({ block: 'end',  behavior: 'smooth' });});
    }
    
    const getQuiz = async () => {
		const quiz = await jq.ajax({
            type: 'GET',
			url: basicURL + `api-get-quiz-questions.php`,
			dataType: "json",
			data: {
                quizID: quiz_id,
				token: localStorage.token
			},
			success: (data) => {
                quizData = data;
                quizData.removedQuestions = [];
                return quizData;
			},
			error: error => {
				// console.log(error);
			}
		});
		if (quiz) {
			return quiz;
		} else {
            throw new Error();
		}
	}

    let promiseQuiz = getQuiz();
    
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
    .add_new_question_container{
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }


    #show_add_question_button{
        font-size: 15px;
        color: rgba(147, 69, 216, 0.445);
        text-decoration: underline;
        margin-left: .1rem;
        cursor: pointer;
        margin-top: 1.5rem;
    }
    .wrapper{
        height: 70vh;
        background: white;
        overflow-y: scroll;
        padding: .7rem;
    }
	.inputElement textarea, .inputElement input {
		resize: none;
		outline: none;
		width: 100%;
		border-radius: 4px;
    }
    

	label{
		margin: .45rem 0;
		color: #9345D8;
		font-weight: bold;
		margin-bottom: .66rem;
    	padding-left: .25rem;
    }
    
    label.quiz_name{
        font-size: 20px;
        color: #E57E39;;
    }

	#filter_container{
		align-items: flex-end;
	}

	#filter_container select{
		width: 195px;
	}

	.add_question_button{
		outline: none;
        border: none;
        cursor: pointer;
        color: white;
        background: #E57E39;;
        padding: 5px 18px;
        font-size: 15px;
	}

	.purple_button{
        margin: 1.7rem 0.7rem;
        width: 100%;
        width: 20%;
        padding: 5px 20px;
    }

    .edit_button_container{
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .orange_button{
        margin-top: 1.7rem;
        padding: 0.3rem .95rem;
        background: #E57E39;
    }

	.wrap_input_container{
        margin-bottom: 1rem;
    }
    
    .wrap_question{
        border: 1px solid rgba(128, 0, 128, 0.136);
        border-radius: 4px;
        margin-bottom: 2rem;
        padding: 0.75rem;
    }
</style>

{#await promiseQuiz}
    <div class="loader">Loading...</div>
{:then quiz}
    <div class="wrap_input_container">
        <div class="inputElement">
            <label for="text" class="quiz_name">
                Quiz name
            </label>
            <input type="text" bind:value={quizData.quizName} on:input={() => validateInput(quizData.quizName, 'name')}/>
        </div>
        {#if showError(quizData.quizName) }
            <div class="error">More than one character!</div>
        {/if}
    </div>
<div class="wrapper">
    {#each quiz.questions as question, i} 
    <div class="wrap_question">
        <div class="wrap_input_container">
                <div class="inputElement">
                    <label for="text">
                        Question {i+1}
                    </label>
                    <textarea type="text" bind:value={quiz.questions[i].questionContent} on:input={() => validateInput(quiz.questions[i].questionContent, '')}></textarea>
                </div>
                {#if showError(quiz.questions[i].questionContent) }
                    <div class="error">More than one character!</div>
                {/if}
            </div>

            <div class="wrap_input_container">
                <div class="inputElement">
                    <label for="lastName">
                        Correct answer
                    </label>
                        <textarea id="lastName" bind:value={quiz.questions[i].questionAnswer} name="questionAnswer" on:input={() => validateInput(quiz.questions[i].questionAnswer, '')}></textarea>
                </div>
                {#if showError(quiz.questions[i].questionAnswer) }
                    <div class="error">More than one character!</div>
                {/if}
            </div>

            <div id="filter_container">
                <div class="container_left_side">
                    <label for="questionDifficulty">Choose question difficulty</label>
                    <select bind:value={quiz.questions[i].questionDifficulty} name="questionDifficulty" on:change={() => validateInput(quiz.questions[i].questionDifficulty, '')}>
                        {#each difficultyLevel as level}
                            <option value={level.value}>
                                {level.text}
                            </option>
                        {/each}
                    </select>
                </div>
                <button class="remove_question_button orange_button" on:click={() => removeQuestion(quiz.questions[i].questionID) } >Remove question</button>
            </div>
            {#if quiz.questions[i].questionDifficulty === "0" }
			    <div class="error">Please select difficulty</div>
		    {/if}
    </div>
    {/each}
    {#if addNewQuestion}
        <div class="add_new_question">
            <div class="wrap_input_container">
                <div class="inputElement">
                    <label for="text">
                        Question
                    </label>
                    <textarea type="text" bind:value={questionValue} placeholder="Enter your question here" on:input={() => validateInput(questionValue, 'question')}></textarea>
                </div>
                {#if (showError(questionValue) && questionWasTouched) || (showError(questionValue) && triedWithEmpty) }
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
		    </div>

            {#if (diffWasTouched && questionDifficulty === "0") || (triedWithEmpty && questionDifficulty === "0") }
                <div class="error">Please select difficulty</div>
            {/if}
            
        </div>
    {/if}

    <div class="add_new_question_container">
        <div on:click={ showAddQuestion } id="show_add_question_button">Add a new question</div>
        {#if addNewQuestion}
            <button on:click={ addQuestion } class="add_question_button">╋ &nbsp;  Add question</button>
        {/if}
    </div>
</div>
    {#if quiz.questions.length > 1 && canEditQuiz }
    <div class="edit_button_container">
		<button on:click={ editQuiz } class="purple_button">Edit quiz</button>
    </div>
	{/if}
{:catch error}
	<p style="color: red">{error.message}</p>
{/await}

