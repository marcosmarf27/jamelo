<!DOCTYPE html>
<html>
<head>
  <style type="text/css">
   html, body{
  margin: 5px;
  padding: 5px;
  background-color: #252539;
  min-height: 80px;
}

div{
  display: flex;
  justify-content: center;
}

button{
  background-color: #e93e8b;
  color: white;
  border: none;
  padding: 10px 25px;
  border-radius: 3px;
  cursor: pointer;
}
button:hover{
  background-color: #d2387d;
}
  </style>
</head>
<body>
<audio id="audio" autoplay>
   <source src="./app/resources/alert.wav" type="audio/mp3" />
</audio>

<div>
  <button id="startButton" >Iniciar</button>
  <button id="stopButton" >Parar</button>
</div>


<script>

const startButton = document.querySelector('#startButton'),
  stopButton = document.querySelector('#stopButton');

let context = new AudioContext(),
	oscillator = context.createOscillator();

function start(){
	startButton.style.display = 'none';
  stopButton.style.display = 'block';
	context = new AudioContext();
	oscillator = context.createOscillator();
	oscillator.type = "sine";
	oscillator.connect(context.destination);
	oscillator.start();
}

function stop(){
	startButton.style.display = 'block';
  stopButton.style.display = 'none';
	oscillator.stop(context.currentTime + 0);
}

startButton.addEventListener('click', start);
stopButton.addEventListener('click', stop);


</script>
</body>
</html>


