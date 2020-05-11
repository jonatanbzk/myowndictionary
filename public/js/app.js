function toggleForm(curr, next) {
    var currentQuestion = document.getElementById(curr);
    var nextQuestion = document.getElementById(next);
        currentQuestion.style.display = 'none';
        nextQuestion.style.display = 'block';

}