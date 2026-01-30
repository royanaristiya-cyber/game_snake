<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>GAME KALA GABUT 😎</title>
  <style>
    :root{
      --bg: #e2ecec;
      --panel: #ffffff;
      --accent: #4caf7d;
      --muted: #555;
      --danger:#f44336;
      font-family: Inter, ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
    }
    *{box-sizing:border-box}
    html,body{height:100%;margin:0;background:linear-gradient(180deg,var(--bg),#d0e0db);display:flex;align-items:center;justify-content:center}
    .wrap{width:min(900px,96vw);background:var(--panel);border-radius:14px;box-shadow:0 8px 30px rgba(13,38,35,0.15);padding:18px;display:flex;gap:18px;align-items:center}
    canvas{background:linear-gradient(180deg,#f1f8f4,#e4f0ea);border-radius:10px;border:1px solid rgba(0,0,0,0.1);display:block}
    .side{width:280px}
    h1{font-size:20px;margin:0 0 8px;color:#333}
    p.small{margin:6px 0;color:var(--muted);font-size:13px}
    .stat{display:flex;gap:10px;margin-top:8px}
    .chip{background:#fafafa;border:1px solid rgba(0,0,0,0.1);padding:8px 10px;border-radius:10px;font-weight:600;color:#333}
    .btn{display:inline-block;padding:8px 12px;border-radius:10px;border:none;background:var(--accent);color:white;font-weight:700;cursor:pointer}
    .btn.ghost{background:transparent;border:1px solid rgba(0,0,0,0.2);color:var(--muted)}
    .controls{display:flex;gap:8px;margin-top:12px}
    .touch{display:none;margin-top:12px}
    .touch .row{display:flex;gap:8px}
    .touch button{flex:1;padding:12px;border-radius:10px;background:#fff;border:1px solid rgba(0,0,0,0.2);font-size:18px}
    footer{margin-top:14px;font-size:12px;color:var(--muted)}
    @media (max-width:820px){
      .wrap{flex-direction:column;align-items:center}
      .side{width:100%}
      .touch{display:block}
      canvas{width:96vw;height:64vw}
    }
  </style>
</head>
<body>
  <div class="wrap">
    <!-- map diperbesar ke 800x600 -->
    <canvas id="game" width="800" height="600"></canvas>
    <div class="side">
      <h1>GAME KALA GABUT<span style="font-size:14px"> 😎</span></h1>
      <p class="small">Main nyantai, jangan buru-buru. Panah / WASD untuk gerak. <strong>Space</strong> untuk pause.</p>

      <div class="stat">
        <div class="chip">Score: <span id="score">0</span> ✨</div>
        <div class="chip">Level: <span id="level">1</span></div>
      </div>

      <div class="controls">
        <button id="btn-start" class="btn">Start</button>
        <button id="btn-pause" class="btn ghost">Pause</button>
        <button id="btn-reset" class="btn ghost">Reset</button>
      </div>

      <div class="touch" aria-hidden="true">
        <div class="row"><button id="t-up">▲</button></div>
        <div class="row">
          <button id="t-left">◀</button>
          <button id="t-down">▼</button>
          <button id="t-right">▶</button>
        </div>
      </div>

      <footer>
        <div style="margin-bottom:6px">Tips: makan 😍 buat poin. Nabrak badan sendiri cuma dipotong, nggak langsung mati. Santai wae.</div>
      </footer>
    </div>
  </div>

<script>
const canvas = document.getElementById('game');
const ctx = canvas.getContext('2d');
let W = canvas.width, H = canvas.height;
const gridSize = 20;
let cols = Math.floor(W / gridSize);
let rows = Math.floor(H / gridSize);

let snake = [{x: Math.floor(cols/2), y: Math.floor(rows/2)}];
let dir = {x:1,y:0}, nextDir = {...dir};
let food = null, running = false, paused = false;
let score = 0, level = 1, speed = 8;

function resetGame(){
  cols = Math.floor(W / gridSize);
  rows = Math.floor(H / gridSize);
  snake = [{x: Math.floor(cols/2), y: Math.floor(rows/2)}];
  dir = {x:1,y:0}; nextDir = {...dir};
  score = 0; level = 1; speed = 8;
  spawnFood();
  updateUI();
}
function spawnFood(){
  while(true){
    const x = Math.floor(Math.random()*cols);
    const y = Math.floor(Math.random()*rows);
    if(!snake.some(s=>s.x===x && s.y===y)){ food = {x,y}; break; }
  }
}
function drawRoundedRect(x,y){
  const px = x*gridSize, py = y*gridSize;
  ctx.fillRect(px,py,gridSize,gridSize);
}
function render(){
  ctx.fillStyle = '#d9ede2';
  ctx.fillRect(0,0,W,H);

  if(food){
    ctx.font = `${gridSize-2}px serif`;
    ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
    ctx.fillText('😍', food.x*gridSize + gridSize/2, food.y*gridSize + gridSize/2 + 1);
  }

  for(let i=snake.length-1;i>=0;i--){
    const s = snake[i];
    ctx.fillStyle = i===0 ? '#2e7d32' : '#4caf50';
    drawRoundedRect(s.x, s.y);
  }

  ctx.strokeStyle = 'rgba(0,0,0,0.05)';
  for(let x=0;x<=cols;x++){ctx.beginPath();ctx.moveTo(x*gridSize,0);ctx.lineTo(x*gridSize,H);ctx.stroke();}
  for(let y=0;y<=rows;y++){ctx.beginPath();ctx.moveTo(0,y*gridSize);ctx.lineTo(W,y*gridSize);ctx.stroke();}
}
function step(){
  if(!running || paused) return;
  dir = nextDir;
  const head = {x: snake[0].x + dir.x, y: snake[0].y + dir.y};
  if(head.x < 0) head.x = cols-1;
  if(head.x >= cols) head.x = 0;
  if(head.y < 0) head.y = rows-1;
  if(head.y >= rows) head.y = 0;
  const hit = snake.findIndex(s=>s.x===head.x && s.y===head.y);
  if(hit>-1){ snake.splice(hit); }
  snake.unshift(head);

  if(food && head.x===food.x && head.y===food.y){
    score+=10;
    if(score % 50 === 0){ level++; speed = Math.min(20, speed+1); }
    spawnFood();
  } else { snake.pop(); }
  updateUI();
}
function updateUI(){
  document.getElementById('score').textContent=score;
  document.getElementById('level').textContent=level;
}
let last=0;
function loop(ts){
  if(!last) last=ts;
  const elapsed=ts-last, interval=1000/speed;
  if(elapsed>interval){ last=ts-(elapsed%interval); step(); render(); }
  requestAnimationFrame(loop);
}
window.addEventListener('keydown',e=>{
  if(['ArrowUp','ArrowDown','ArrowLeft','ArrowRight',' '].includes(e.key)) e.preventDefault();
  if(e.key===' '){ paused=!paused; document.getElementById('btn-pause').textContent=paused?'Resume':'Pause'; return; }
  const map={ArrowUp:[0,-1],ArrowDown:[0,1],ArrowLeft:[-1,0],ArrowRight:[1,0],w:[0,-1],s:[0,1],a:[-1,0],d:[1,0]};
  const k=e.key.toLowerCase(); if(map[e.key]) setNextDir(map[e.key]); else if(map[k]) setNextDir(map[k]);
});
function setNextDir([nx,ny]){
  if(nx===-dir.x && ny===-dir.y) return; nextDir={x:nx,y:ny};
}
['t-up','t-down','t-left','t-right'].forEach(id=>{
  document.getElementById(id).addEventListener('touchstart',e=>{
    e.preventDefault(); const map={'t-up':[0,-1],'t-down':[0,1],'t-left':[-1,0],'t-right':[1,0]}; setNextDir(map[id]);
  });
});
document.getElementById('btn-start').addEventListener('click',()=>{running=true; paused=false;});
document.getElementById('btn-pause').addEventListener('click',()=>{if(!running) return; paused=!paused; document.getElementById('btn-pause').textContent=paused?'Resume':'Pause';});
document.getElementById('btn-reset').addEventListener('click',()=>{resetGame(); running=false; paused=false; render();});

resetGame(); render(); requestAnimationFrame(loop);
</script>
</body>
</html>
