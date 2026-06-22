#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Musique d'habillage (originale, libre de droits) — style NAPOLITAIN / italien (clin d'œil à Naples).
Mandoline tremolo (mélodie) + guitare arpégée + basse + tambourin léger. Ensoleillé, La majeur.
Sortie : tools/ads-videos/music.wav  (~15.5 s, stéréo 44.1 kHz)
"""
import os, wave, numpy as np

HERE = os.path.dirname(os.path.abspath(__file__))
SR = 44100

F = {  # fréquences (Hz)
 'A2':110.00,'E2':82.41,'D2':73.42,'Fs2':92.50,'B2':123.47,'E3':164.81,'A3':220.00,'D3':146.83,'Fs3':185.00,
 'Cs4':277.18,'E4':329.63,'A4':440.00,'B4':493.88,'D4':293.66,'Fs4':369.99,'Gs3':207.65,
 'Cs5':554.37,'D5':587.33,'E5':659.25,'Fs5':739.99,'Gs5':830.61,'A5':880.00,'B5':987.77,'Cs6':1108.73,
}

def env_adsr(n, a, d, s, r):
    e=np.ones(n); na,nd,nr=int(a*SR),int(d*SR),int(r*SR)
    if na: e[:na]=np.linspace(0,1,na)
    if nd: e[na:na+nd]=np.linspace(1,s,nd)
    e[na+nd:n-nr]=s
    if nr: e[n-nr:]=np.linspace(s,0,nr)
    return e

def mandolin(freq, dur):
    """Mandoline : tremolo (AM rapide) + vibrato léger + harmoniques brillantes."""
    n=int(dur*SR); t=np.arange(n)/SR
    vib=1+0.005*np.sin(2*np.pi*5.5*t)
    s=np.zeros(n)
    for h,amp in [(1,1.0),(2,0.6),(3,0.35),(4,0.22),(5,0.12),(6,0.07)]:
        s+=amp*np.sin(2*np.pi*freq*h*vib*t)
    s/=2.4
    trem=0.72+0.28*(0.5+0.5*np.sin(2*np.pi*13.0*t))   # tremolo ~13 Hz
    e=env_adsr(n, 0.004, 0.05, 0.85, min(0.12,dur*0.4))
    return s*trem*e

def guitar(freq, dur):
    n=int(dur*SR); t=np.arange(n)/SR; s=np.zeros(n)
    for h,amp in [(1,1.0),(2,0.4),(3,0.18),(4,0.08)]:
        s+=amp*np.sin(2*np.pi*freq*h*t)
    s/=1.7
    e=np.exp(-t/ (dur*0.5)) * env_adsr(n,0.003,0.02,0.7,dur*0.3)
    return s*e*0.9

def bass(freq, dur):
    n=int(dur*SR); t=np.arange(n)/SR
    s=np.sin(2*np.pi*freq*t)+0.25*np.sin(2*np.pi*freq*2*t)
    e=np.exp(-t/(dur*0.6))*env_adsr(n,0.005,0.05,0.7,dur*0.3)
    return s*e*0.8

def tamb(dur, level=0.25):
    n=int(dur*SR); t=np.arange(n)/SR
    noise=np.random.uniform(-1,1,n)
    # bandpass grossier : différence de moyennes glissantes
    k=8; nz=np.convolve(noise,np.ones(k)/k,'same'); nz=noise-nz
    e=np.exp(-t/0.04)
    return nz*e*level

BEAT=0.46  # ~130 BPM (vif, napolitain)
# progression (1 accord / mesure de 4 temps = 2 s)
PROG=['A','E','D','A','Fsm','D','E','A']
CHORDS={'A':['A3','Cs4','E4','A4'],'E':['E3','Gs3','B4','E4'],
        'D':['D3','Fs3','A3','D4'],'Fsm':['Fs3','A3','Cs4','Fs4']}
ROOT={'A':'A2','E':'E2','D':'D2','Fsm':'Fs2'}

# mélodie mandoline : (note ou None, durée en temps), originale, ensoleillée
MELODY=[
 ('A4',1),('Cs5',1),('E5',1),('A5',1),          # M1 A
 ('Gs5',1),('Fs5',1),('E5',2),                  # M2 E
 ('Fs5',1),('E5',1),('D5',1),('Fs5',1),         # M3 D
 ('E5',2),('Cs5',2),                            # M4 A
 ('A5',1),('Gs5',1),('Fs5',1),('E5',1),         # M5 F#m
 ('D5',1),('E5',1),('Fs5',2),                   # M6 D
 ('E5',1),('Fs5',1),('Gs5',1),('B4',1),         # M7 E
 ('A4',4),                                      # M8 A (résolution)
]

total = 8*4*BEAT + 0.6
N=int(total*SR)
L=np.zeros(N); Rr=np.zeros(N)
def place(buf,sig,start,gain=1.0):
    i=int(start*SR); j=min(N,i+len(sig)); buf[i:j]+=sig[:j-i]*gain

# accompagnement par mesure
for m,ch in enumerate(PROG):
    t0=m*4*BEAT
    # basse temps 1 et 3
    for b in (0,2):
        bs=bass(F[ROOT[ch]], BEAT*1.8)
        place(L,bs,t0+b*BEAT,0.9); place(Rr,bs,t0+b*BEAT,0.9)
    # guitare : arpège croches montant/descendant (léger pan)
    tones=CHORDS[ch]; seq=tones+tones[::-1]
    for i in range(8):
        g=guitar(F[seq[i%len(seq)]], BEAT*1.4)
        st=t0+i*(BEAT/2)
        place(L,g,st,0.55 if i%2==0 else 0.4)
        place(Rr,g,st,0.4 if i%2==0 else 0.55)
    # tambourin temps 2 et 4
    for b in (1,3):
        tb=tamb(0.18); place(L,tb,t0+b*BEAT,0.8); place(Rr,tb,t0+b*BEAT,0.8)

# mélodie mandoline (au-dessus), centrée
t=0.0
for note,beats in MELODY:
    dur=beats*BEAT
    if note:
        s=mandolin(F[note], dur*0.98)
        place(L,s,t,0.85); place(Rr,s,t,0.85)
    t+=dur

# réverb légère
def reverb(x):
    out=x.copy()
    for dl,g in [(0.029,0.3),(0.041,0.24),(0.062,0.18),(0.089,0.12)]:
        d=int(dl*SR); e=np.zeros_like(x); e[d:]=x[:-d]*g; out+=e
    return out
L=reverb(L); Rr=reverb(Rr)

# fondu + normalisation
fin,fout=int(0.15*SR),int(1.6*SR)
fade=np.ones(N); fade[:fin]=np.linspace(0,1,fin); fade[-fout:]=np.linspace(1,0,fout)**1.4
L*=fade; Rr*=fade
peak=max(np.max(np.abs(L)),np.max(np.abs(Rr))) or 1.0
L=(L/peak)*0.9; Rr=(Rr/peak)*0.9

pcm=(np.stack([L,Rr],axis=1)*32767).astype('<i2')
out=os.path.join(HERE,"music.wav")
with wave.open(out,'wb') as w:
    w.setnchannels(2); w.setsampwidth(2); w.setframerate(SR); w.writeframes(pcm.tobytes())
print("OK", out, os.path.getsize(out)//1024, "Ko", "| durée %.1fs"%total)
