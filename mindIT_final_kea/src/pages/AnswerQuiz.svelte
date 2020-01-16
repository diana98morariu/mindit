<script>
	import jq from "jquery";
    import Questions from "../components/Questions.svelte";

    const urlParams = new URLSearchParams(window.location.search);
    const quiz_id = urlParams.get('id');
    const basicURL = '../../../backend/mindIT_backend/apis/';
    
    const getQuiz = async () => {
		const quiz = await jq.ajax({
			type: 'GET',
			url: basicURL + `api-get-quiz.php`,
			dataType: "json",
			data: {
                quizID: quiz_id,
				token: localStorage.token
			},
			success: (data) => {
				return data;
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

	const promiseQuiz = getQuiz();

</script>
<style>
    .answer_quiz_top_bar{
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 2rem 0;
    }
  
    .answer_quiz_name{
        font-size: 2rem;
        font-weight: bold;
        text-transform: capitalize;
        margin-right: 1rem;
    }

    .answer_quiz_difficulty {
        color: rgb(117, 117, 117);
        font-size: 1.15rem;
        margin-top: 1rem;
    }


</style>

{#await promiseQuiz}
    <div class="loader">Loading...</div>
{:then quiz}
    <div class="answer_quiz_top_bar">
        <div class="answer_quiz_top_bar_left">
            <div class="answer_quiz_name">{quiz.name}</div>
            <div class="answer_quiz_difficulty">Difficulty - {quiz.difficulty}</div>
        </div>
        <div class="answer_quiz_user_name">created by {quiz.firstName} {quiz.lastName}</div>
    </div>
    <Questions quizID={quiz.id}></Questions>
{:catch error}
	<p style="color: red">{error.message}</p>
{/await}

