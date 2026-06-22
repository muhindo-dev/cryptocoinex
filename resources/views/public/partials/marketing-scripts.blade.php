<script>
(function(){
  'use strict';
  // ── Animated chart helper (random walk, redraws each tick) ──
  function mkChart(lineId, areaId, opts){
    var line=document.getElementById(lineId), area=document.getElementById(areaId);
    if(!line) return null;
    var W=opts.w, H=opts.h, N=opts.n, pad=opts.pad||10, data=[], base=opts.base||100;
    for(var i=0;i<N;i++){ base += (Math.random()-0.46)*opts.vol; data.push(base); }
    function draw(){
      var min=Math.min.apply(null,data), max=Math.max.apply(null,data), rng=(max-min)||1;
      var step=W/(N-1), pts=data.map(function(v,i){ var x=i*step; var y=pad+(H-2*pad)*(1-(v-min)/rng); return [x,y]; });
      var d=pts.map(function(p,i){ return (i?'L':'M')+p[0].toFixed(1)+','+p[1].toFixed(1); }).join(' ');
      line.setAttribute('d',d);
      if(area) area.setAttribute('d', d+' L'+W+','+H+' L0,'+H+' Z');
      return pts[pts.length-1];
    }
    return { data:data, draw:draw, tick:function(){ data.push(data[data.length-1]+(Math.random()-0.48)*opts.vol); data.shift(); return draw(); } };
  }

  var hero = mkChart('hcLine','hcArea',{w:460,h:188,n:46,pad:16,base:67400,vol:48});
  var dot=document.getElementById('hcDot'), tag=document.getElementById('hcTag'),
      price=document.getElementById('hcPrice'), chg=document.getElementById('hcChg'), pnl=document.getElementById('hcPnl');
  function fmt(n){ return Math.round(n).toLocaleString('en-US'); }
  if(hero){
    var last=hero.draw(); var prevVal=hero.data[hero.data.length-1];
    function place(p){ if(dot){dot.setAttribute('cx',p[0]);dot.setAttribute('cy',p[1]);} if(tag){tag.style.top=p[1]+'px';} }
    place(last);
    setInterval(function(){
      var p=hero.tick(); place(p);
      var v=hero.data[hero.data.length-1], up=v>=prevVal;
      var col = up?'var(--grn)':'var(--red)';
      if(price){ price.textContent=fmt(v); price.style.color=col; }
      if(tag){ tag.textContent=fmt(v); tag.style.background = up?'rgba(22,210,145,.92)':'rgba(255,77,106,.92)'; }
      if(chg){ var pc=((v-67400)/67400*100); chg.textContent=(pc>=0?'▲ ':'▼ ')+Math.abs(pc).toFixed(2)+'%'; chg.style.color=pc>=0?'var(--grn)':'var(--red)'; }
      if(pnl){ pnl.textContent = v>67388?'▲ Winning':'▼ Losing'; pnl.style.color = v>67388?'var(--grn)':'var(--red)'; }
      prevVal=v;
    },1100);
  }

  // Static-but-pretty mini charts (features bento + showcase)
  var mini = mkChart('bLine','bArea',{w:600,h:160,n:54,pad:10,base:120,vol:7}); if(mini) mini.draw();
  var shw  = mkChart('sLine','sArea',{w:900,h:280,n:80,pad:18,base:200,vol:9}); if(shw) shw.draw();

  // ── Marquee ──
  var assets=[['BT','#f7931a','BTC/USD','67,431','▲ 1.24%',1],['ET','#627eea','ETH/USD','3,512','▲ 0.86%',1],
    ['SO','#14f195','SOL/USD','151.4','▲ 2.10%',1],['XAU','#d4af37','Gold','2,351','▼ 0.31%',0],
    ['BN','#f0b90b','BNB/USD','601.2','▲ 0.44%',1],['XAG','#9ca3af','Silver','30.6','▲ 0.92%',1],
    ['EUR','#3b82f6','EUR/USD','1.0852','▼ 0.08%',0],['TS','#cc0000','TSLA','244.9','▲ 1.77%',1]];
  var track=document.getElementById('mqTrack');
  if(track){
    var html=assets.map(function(a){ return '<span class="mq-item"><span class="ic" style="background:linear-gradient(135deg,'+a[1]+',rgba(0,0,0,.4))">'+a[0]+'</span>'+a[2]+' <b>'+a[3]+'</b> <span class="'+(a[5]?'mq-up':'mq-dn')+'">'+a[4]+'</span></span>'; }).join('');
    track.innerHTML=html+html; // duplicate for seamless loop
  }

  // ── Animated counters ──
  function animate(el){
    var target=parseFloat(el.dataset.count), suf=el.dataset.suffix||'', dur=1100, t0=null;
    function frame(t){ if(!t0)t0=t; var k=Math.min((t-t0)/dur,1); var e=1-Math.pow(1-k,3);
      el.textContent=Math.round(target*e).toLocaleString('en-US')+suf; if(k<1)requestAnimationFrame(frame); }
    requestAnimationFrame(frame);
  }
  var cio=new IntersectionObserver(function(es){ es.forEach(function(e){ if(e.isIntersecting){ animate(e.target); cio.unobserve(e.target); } }); },{threshold:.5});
  document.querySelectorAll('[data-count]').forEach(function(el){ cio.observe(el); });

  // ── FAQ accordion ──
  document.querySelectorAll('.qa-q').forEach(function(q){
    q.addEventListener('click', function(){
      var qa=q.parentElement, a=qa.querySelector('.qa-a'), open=qa.classList.contains('open');
      document.querySelectorAll('.qa.open').forEach(function(o){ o.classList.remove('open'); o.querySelector('.qa-a').style.maxHeight=null; });
      if(!open){ qa.classList.add('open'); a.style.maxHeight=a.scrollHeight+'px'; }
    });
  });
})();
</script>
