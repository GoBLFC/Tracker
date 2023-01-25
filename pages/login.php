<?php
/**
 * Created by PhpStorm.
 * User: joann
 * Date: 1/29/2019
 * Time: 11:27 PM
 */
if (!defined('TRACKER')) die('No.');

//header("Refresh:2; url=/sso", true, 303);
?>

<div class="card" style="width: 28rem;top: 8em;">
    <div class="card-body">
        <p class="card-text">
        <h2>BLFC Volunteer Check-In</h2></p>
        <p class="card-text">
        <div class="alert alert-dark" role="alert" style="text-align: center">Welcome! Click below to sign in.</div>
        <a role="button" class="btn btn-success btn-sm" href="/sso" style="width: 100%">Sign In</a>
        </p>
		<br>
		<form id="form">
		  <h4 class="text-center mb-4">Quick Sign In Code <span data-toggle="tooltip" data-placement="top" title="" data-original-title="Link your Telegram account after you sign in above to get a quick code anytime!">❔</span></h4>
		  <div class="d-flex mb-3">
			<input type="tel" maxlength="1" pattern="[0-9]" id="c1" class="form-control" autofocus="on">
			<input type="tel" maxlength="1" pattern="[0-9]" id="c2" class="form-control">
			<input type="tel" maxlength="1" pattern="[0-9]" id="c3" class="form-control">
			<input type="tel" maxlength="1" pattern="[0-9]" id="c4" class="form-control">
		  </div>
		  <button type="button" class="w-100 btn btn-primary" id="btn">Sign In</button>
		</form>
    </div>
</div>

<script>
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})

const form = document.querySelector('form')
const inputs = form.querySelectorAll('input')
const KEYBOARDS = {
  backspace: 8,
  arrowLeft: 37,
  arrowRight: 39,
  enter: 13,
}

var btn = document.getElementById("btn");

btn.addEventListener("click", function() {
    submitCode()
});

function submitCode() {
	var code = "";
	inputs.forEach((input, i) => {code += input.value});
	
    postAction({action: 'checkQuickCode', quickcode: code}, function (data) {
        if (data['code'] === 1) {
			// Successful login, forward to landing
			document.cookie = "badge=" + data['id'];
			document.cookie = "session=" + data['session'];
			location.reload();
			//toastNotify('Good Code!', 'success', 1500);
        } else {
            toastNotify('Invalid Code', 'warning', 1500);
			inputs.forEach((input, i) => {input.value = ""});
			inputs[0].focus();
        }
    });
}

function handleInput(e) {
  const input = e.target
  const nextInput = input.nextElementSibling
  if (nextInput && input.value) {
    nextInput.focus()
    if (nextInput.value) {
      nextInput.select()
    }
  }
}

function handlePaste(e) {
  e.preventDefault()
  const paste = e.clipboardData.getData('text')
  inputs.forEach((input, i) => {
    input.value = paste[i] || ''
  })
}

function handleBackspace(e) { 
  const input = e.target
  if (input.value) {
    input.value = ''
    return
  }
  
  input.previousElementSibling.focus()
}

function handleArrowLeft(e) {
  const previousInput = e.target.previousElementSibling
  if (!previousInput) return
  previousInput.focus()
}

function handleArrowRight(e) {
  const nextInput = e.target.nextElementSibling
  if (!nextInput) return
  nextInput.focus()
}

function handleEnter(e) {
  submitCode();
}

form.addEventListener('input', handleInput)
inputs[0].addEventListener('paste', handlePaste)

inputs.forEach(input => {
  input.addEventListener('focus', e => {
    setTimeout(() => {
      e.target.select()
    }, 0)
  })
  
  input.addEventListener('keydown', e => {
    switch(e.keyCode) {
      case KEYBOARDS.backspace:
        handleBackspace(e)
        break
      case KEYBOARDS.arrowLeft:
        handleArrowLeft(e)
        break
      case KEYBOARDS.arrowRight:
        handleArrowRight(e)
        break
      case KEYBOARDS.enter:
        handleEnter(e)
        break
      default:  
    }
  })
})
</script>

<style>
form {
  padding: 2rem;
  border-radius: 4px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  max-width: 400px;
  background: #505050;
}
form .form-control {
  display: block;
  height: 50px;
  margin-right: 0.5rem;
  text-align: center;
  font-size: 1.25rem;
  min-width: 0;
}
form .form-control:last-child {
  margin-right: 0;
}
</style>