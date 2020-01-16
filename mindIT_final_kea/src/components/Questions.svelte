<script>
    import jq from "jquery";
    import toastr from "toastr";
    import { curRoute } from '../routing/router.js';

    toastr.options = {
        "positionClass": "toast-bottom-right",
    }
    const basicURL = '../../../backend/mindIT_backend/apis/';

    export let quizID;

    let correctAnswers, wrongAnswers, questions, percentage;
    
    const getQuestions = async () => {
		const quiz = await jq.ajax({
            type: 'GET',
			url: basicURL + `api-get-quiz-questions.php`,
			dataType: "json",
			data: {
                quizID: quizID,
				token: localStorage.token
			},
			success: (data) => {
                return data;
			},
			error: error => {
				//console.log(error);
			}
		});
		if (quiz) {
			return quiz;
		} else {
            throw new Error();
		}
	}

    const promiseQuestions = getQuestions();
    
    function checkQuiz(){
        promiseQuestions.then((quiz) => {
            const isFormValid = validateInputs();
            if(!isFormValid) {
                return;
            }
            let userAnswers = [];
            quiz.questions.forEach(question => {
                const userAnswer = jq(`#${question.questionID}`).val();
                let questionObject = {};
                questionObject.questionID = question.questionID;
                questionObject.questionUserAnswer = userAnswer;
                userAnswers.push(questionObject);
            });
            jq.ajax({
                type: 'GET',
                url: basicURL + 'api-check-answered-quiz.php',
                dataType: "json",
                data: {
                    id: quizID,
                    questions: JSON.stringify(userAnswers),
                    token: localStorage.token
                },
                success: (response) => {
                    // console.log(response);
                    if(response.message === 'user completed quiz before'){
                        toastr.error('You cannot complete the same quiz twice');
                        curRoute.set('/home');
		                window.history.pushState({path: '/home'}, '', window.location.origin + '/home');
                    } else {
                        correctAnswers = response.correctAnswers.length;
                        wrongAnswers = response.wrongAnswers.length;
                        questions = correctAnswers + wrongAnswers;
                        percentage = correctAnswers * 100 / questions;
                        jq('.purple_button.submit').hide();
                        jq('.results').show();

                        response.correctAnswers.forEach(correctAnswer => {
                            jq(`#${correctAnswer.id}`).parent().addClass('success_question');
                        });
                        response.wrongAnswers.forEach(wrongAnswer => {
                            jq(`#${wrongAnswer.id}`).parent().addClass('fail_question');
                            jq(`.${wrongAnswer.id}`).show();
                            jq(`.${wrongAnswer.id}`).text(`Correct answer: ${wrongAnswer.correctAnswer}`);
                            jq(`.${wrongAnswer.id}`).addClass('success_question');
                        });
                    }
                },
                error: error => {
                    // console.log(error);
                }
            });
        });
    }

    function toHomePage(){
		curRoute.set('/home');
		window.history.pushState({path: '/home'}, '', window.location.origin + '/home');
	}

    function validateInputs() {
        const inputs = jq('.question_container input');
        let formIsValid = true;
        inputs.each((index, input) => {
            if(input.value === ''){
                input.classList.add('error_message');
                formIsValid = false;
            }
        });
        inputs.focus((event) => {
            event.target.classList.remove('error_message');
        });
        return formIsValid;
    }

</script>
<style>
    .question_container{
        border: 1px solid rgba(2, 2, 2, 0.1);
        padding: 1rem;
        border-radius: 4px;
        margin: .5rem 0;
    }

    .question_title, .question_content{
        margin-bottom: 1rem;
    }

    .question_title{
        color: #9345D8;
        font-weight: bold;
    }

    .question_answer{
        width: 100%;
        padding: .35rem .7rem;
        border-radius: 4px;
        text-transform: capitalize;
    }

    .purple_button {
        width: 100%;
        margin: 1rem 0;
    }

    .results{
        display: none;
        margin: 0.7rem;
        margin-top: 1.5rem;
    }

    .result_title{
        font-size: 20px;
        font-weight: bold;
        margin-bottom: .7rem;
    }

    .result_score span, .result_raport span{
        font-weight: bold;
    }

    .result_raport{
        margin-bottom: .7rem;
    }

    .correct_answer{
        width: 100%;
        padding: .35rem .7rem;
        border-radius: 4px;
        text-transform: capitalize;
        margin-top: .5rem;
        display: none;
    }

</style>

{#await promiseQuestions}
    <div class="loader">Loading...</div>
{:then quiz}
    <div class="content">
        {#each quiz.questions as question, i} 
            <div class="question_container">
                <div class="question_title">Question {i + 1}</div>
                <div class="question_content">{question.questionContent}</div>
                <input id={question.questionID} class="question_answer" type="text" placeholder="Enter your answer here">
                <div class="{question.questionID} correct_answer"></div>
            </div>
        {/each}
        <button class="purple_button submit" on:click={checkQuiz}>Submit</button>
    </div>
    <div class="results">
        <div class="result_title">Your results</div>
        <div class="result_raport">❓You answered correct <span>{correctAnswers}</span> out of <span>{questions}</span> questions!</div>
        <div class="result_score">📜 Percentage of right answers: <span>{percentage}%</span>!</div>
        <button class="purple_button" on:click={toHomePage}>Go back to home page</button>
    </div>
{:catch error}
	<p style="color: red">{error.message}</p>
{/await}

