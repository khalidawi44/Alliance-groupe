<?php
/**
 * Mesh gradient WebGL animé — fond cinématographique pour le hero
 * (effet dégradé animé moderne).
 *
 * Shader fragment GLSL custom : 4 colors mix avec simplex noise animé
 * dans le temps, légèrement influencé par la position de la souris.
 *
 * À inclure DANS un container relatif (le hero) :
 *   get_template_part( 'template-parts/mesh-gradient-bg' );
 *
 * Couleurs Alliance par défaut. Override via filter `ag_mesh_gradient_colors`.
 *
 * @package Alliance_Groupe_Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$ag_mesh_colors = apply_filters( 'ag_mesh_gradient_colors', array(
	'#0a0a0f',  // noir Alliance
	'#1a1a2e',  // navy
	'#D4B45C',  // champagne
	'#F37A1F',  // orange Alliance
) );
$ag_mesh_uid = 'agmesh-' . wp_rand( 1000, 9999 );
?>

<canvas id="<?php echo esc_attr( $ag_mesh_uid ); ?>" class="ag-mesh-gradient"
        data-colors="<?php echo esc_attr( wp_json_encode( $ag_mesh_colors ) ); ?>"
        aria-hidden="true"></canvas>

<style>
.ag-mesh-gradient{
    position:absolute; inset:0;
    width:100%; height:100%;
    z-index:0; pointer-events:none;
    opacity:.85;
}
</style>

<script>
(function(){
    var canvas = document.getElementById('<?php echo esc_js( $ag_mesh_uid ); ?>');
    if (!canvas) return;
    var gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
    if (!gl) { canvas.style.display='none'; return; }
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        canvas.style.display='none'; return;
    }
    // Skip mobile + tablette + low-end pour performance (shader WebGL très coûteux)
    if (window.innerWidth < 1100) { canvas.style.display='none'; return; }
    // Skip si peu de coeurs CPU (mobile/low-end laptops)
    if (navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4) { canvas.style.display='none'; return; }

    var colors = JSON.parse(canvas.dataset.colors);
    function hex2rgb(h){ h=h.replace('#',''); return [parseInt(h.slice(0,2),16)/255, parseInt(h.slice(2,4),16)/255, parseInt(h.slice(4,6),16)/255]; }
    var c0=hex2rgb(colors[0]), c1=hex2rgb(colors[1]), c2=hex2rgb(colors[2]), c3=hex2rgb(colors[3]);

    // Vertex shader : full-screen quad
    var vs = 'attribute vec2 p;void main(){gl_Position=vec4(p,0.,1.);}';

    // Fragment shader : mesh gradient via simplex-like noise mix
    var fs = [
        'precision highp float;',
        'uniform vec2 uRes;',
        'uniform float uTime;',
        'uniform vec2 uMouse;',
        'uniform vec3 c0,c1,c2,c3;',
        'float h(vec2 p){return fract(sin(dot(p,vec2(127.1,311.7)))*43758.5453);}',
        'float n(vec2 p){vec2 i=floor(p),f=fract(p);vec2 u=f*f*(3.-2.*f);return mix(mix(h(i),h(i+vec2(1,0)),u.x),mix(h(i+vec2(0,1)),h(i+vec2(1,1)),u.x),u.y);}',
        'float fbm(vec2 p){float v=0.,a=.5;for(int i=0;i<4;i++){v+=a*n(p);p*=2.;a*=.5;}return v;}',
        'void main(){',
            'vec2 uv=gl_FragCoord.xy/uRes;',
            'vec2 m=uMouse*2.-1.;',
            'float t=uTime*.08;',
            'vec2 p=uv*2.5;',
            'p+=m*.15;',
            'float n1=fbm(p+vec2(t,t*.7));',
            'float n2=fbm(p+vec2(-t*.5,t*1.2)+10.);',
            'float n3=fbm(p+vec2(t*.3,-t)+20.);',
            'vec3 col=mix(c0,c1,smoothstep(.2,.8,n1));',
            'col=mix(col,c2,smoothstep(.4,.7,n2)*.5);',
            'col=mix(col,c3,smoothstep(.55,.85,n3)*.35);',
            // Vignette pour profondeur
            'float vg=1.-length(uv-.5)*.9;',
            'col*=vg;',
            'gl_FragColor=vec4(col,1.);',
        '}'
    ].join('\n');

    function compile(src, type){
        var s = gl.createShader(type); gl.shaderSource(s,src); gl.compileShader(s);
        if(!gl.getShaderParameter(s,gl.COMPILE_STATUS)){ console.warn(gl.getShaderInfoLog(s)); return null; }
        return s;
    }
    var prog = gl.createProgram();
    gl.attachShader(prog, compile(vs, gl.VERTEX_SHADER));
    gl.attachShader(prog, compile(fs, gl.FRAGMENT_SHADER));
    gl.linkProgram(prog);
    if(!gl.getProgramParameter(prog, gl.LINK_STATUS)){ console.warn(gl.getProgramInfoLog(prog)); return; }
    gl.useProgram(prog);

    // Quad full screen
    var buf = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, buf);
    gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1,-1, 1,-1, -1,1,  -1,1, 1,-1, 1,1]), gl.STATIC_DRAW);
    var loc = gl.getAttribLocation(prog,'p');
    gl.enableVertexAttribArray(loc);
    gl.vertexAttribPointer(loc, 2, gl.FLOAT, false, 0, 0);

    var uRes = gl.getUniformLocation(prog,'uRes');
    var uTime = gl.getUniformLocation(prog,'uTime');
    var uMouse = gl.getUniformLocation(prog,'uMouse');
    gl.uniform3f(gl.getUniformLocation(prog,'c0'), c0[0],c0[1],c0[2]);
    gl.uniform3f(gl.getUniformLocation(prog,'c1'), c1[0],c1[1],c1[2]);
    gl.uniform3f(gl.getUniformLocation(prog,'c2'), c2[0],c2[1],c2[2]);
    gl.uniform3f(gl.getUniformLocation(prog,'c3'), c3[0],c3[1],c3[2]);

    var mouse=[.5,.5], tMouse=[.5,.5];
    var host = canvas.parentElement;
    function resize(){
        var dpr=Math.min(window.devicePixelRatio||1,1.5);
        canvas.width = canvas.clientWidth * dpr;
        canvas.height = canvas.clientHeight * dpr;
        gl.viewport(0,0,canvas.width,canvas.height);
        gl.uniform2f(uRes, canvas.width, canvas.height);
    }
    resize();
    new ResizeObserver(resize).observe(canvas);

    // Mousemove désactivé : le mesh gradient continue à s'animer tout seul
    // (uTime), mais ne suit plus la souris — gain CPU important sans perte
    // visible.
    /*
    host && host.addEventListener('mousemove', function(e){
        var r=host.getBoundingClientRect();
        tMouse[0] = (e.clientX - r.left) / r.width;
        tMouse[1] = 1 - (e.clientY - r.top) / r.height;
    });
    */

    var start = performance.now();
    var visible = true, rafId = null;
    // Pause quand le canvas sort du viewport (énorme gain CPU)
    if ('IntersectionObserver' in window){
        new IntersectionObserver(function(entries){
            visible = entries[0].isIntersecting;
            if (visible && !rafId) frame();
        }, { threshold: 0 }).observe(canvas);
    }
    function frame(){
        if (!visible) { rafId = null; return; }
        mouse[0] += (tMouse[0]-mouse[0])*.05;
        mouse[1] += (tMouse[1]-mouse[1])*.05;
        gl.uniform1f(uTime, (performance.now()-start)/1000);
        gl.uniform2f(uMouse, mouse[0], mouse[1]);
        gl.drawArrays(gl.TRIANGLES, 0, 6);
        rafId = requestAnimationFrame(frame);
    }
    frame();
})();
</script>
