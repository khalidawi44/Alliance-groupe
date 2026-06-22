#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Musique d'habillage (libre de droits, générée) pour les vidéos Google Ads Alliance Groupe.
Ambiance cinématique douce noir & or : nappe + arpège façon cloches + basse, réverb légère.
Sortie : tools/ads-videos/music.wav  (~15.5 s, stéréo 44.1 kHz)
"""
import os, numpy as np

HERE = os.path.dirname(os.path.abspath(__file__))
SR = 44100
DUR = 15.5
N = int(SR*DUR)
t = np.arange(N)/SR

def adsr(length, a, d, s_lvl, r):
    n = int(length*SR); env = np.ones(n)
    na, nd, nr = int(a*SR), int(d*SR), int(r*SR)
    if na: env[:na] = np.linspace(0, 1, na)
    if nd: env[na:na+nd] = np.linspace(1, s_lvl, nd)
    env[na+nd:n-nr] = s_lvl
    if nr: env[n-nr:] = np.linspace(s_lvl, 0, nr)
    return env

def note(freq, dur, partials=((1,1.0),(2,0.35),(3,0.14),(4,0.06)), detune=0.004):
    n = int(dur*SR); tt = np.arange(n)/SR; s = np.zeros(n)
    for h, a in partials:
        s += a*np.sin(2*np.pi*freq*h*(1+detune)*tt)
        s += a*0.5*np.sin(2*np.pi*freq*h*(1-detune)*tt)
    return s/ (sum(a for _,a in partials)*1.5)

# notes (Hz)
F = {'C3':130.81,'E3':164.81,'F3':174.61,'G3':196.00,'A3':220.00,'B3':246.94,
     'C4':261.63,'D4':293.66,'E4':329.63,'F4':349.23,'G4':392.00,'A4':440.00,'B4':493.88,
     'C5':523.25,'E5':659.25,'G5':783.99}

# Progression : Cmaj7 - Am7 - Fmaj7 - Gsus  (élégante, se résout)
bars = [
    {'bass':'C3','pad':['C4','E4','G4','B4'],'arp':['C4','E4','G4','B4','C5','G4']},
    {'bass':'A3','pad':['A3','C4','E4','G4'],'arp':['A3','C4','E4','G4','A4','E4']},
    {'bass':'F3','pad':['F3','A3','C4','E4'],'arp':['F3','A3','C4','E4','F4','C4']},
    {'bass':'G3','pad':['G3','B3','D4','G4'],'arp':['G3','B3','D4','G4','B4','D4']},
]
BAR = DUR/len(bars)   # ~3.875 s

L = np.zeros(N); Rr = np.zeros(N)
def place(buf, sig, start):
    i = int(start*SR); j = min(N, i+len(sig)); buf[i:j] += sig[:j-i]

for bi, bar in enumerate(bars):
    t0 = bi*BAR
    # nappe (pad) : accord tenu, attaque douce
    penv = adsr(BAR+0.4, a=0.6, d=0.4, s_lvl=0.8, r=0.8)
    pad = np.zeros(len(penv))
    for nm in bar['pad']:
        nn = note(F[nm], len(penv)/SR, partials=((1,1.0),(2,0.25),(3,0.08)))
        pad[:len(nn)] += nn[:len(pad)]
    pad = pad/len(bar['pad'])*0.5*penv
    place(L, pad, t0); place(Rr, pad, t0)
    # basse
    benv = adsr(BAR+0.2, 0.02, 0.2, 0.7, 0.5)
    bs = note(F[bar['bass']], len(benv)/SR, partials=((1,1.0),(2,0.2)))*0.55*benv
    place(L, bs, t0); place(Rr, bs, t0)
    # arpège façon cloches (croches), léger ping-pong stéréo
    steps = bar['arp']; sd = BAR/len(steps)
    for si, nm in enumerate(steps):
        env = adsr(sd*1.6, 0.005, 0.5, 0.25, sd*0.9)
        bell = note(F[nm]*2, len(env)/SR, partials=((1,1.0),(2,0.5),(3,0.25),(5,0.1)))*0.32*env
        st = t0 + si*sd
        if si % 2 == 0:
            place(L, bell, st); place(Rr, bell*0.6, st)
        else:
            place(L, bell*0.6, st); place(Rr, bell, st)

# scintillement aigu discret tout du long
shim = 0.04*np.sin(2*np.pi*F['G5']*t)*(0.5+0.5*np.sin(2*np.pi*0.15*t))
L += shim*0.5; Rr += shim*0.5

def reverb(x):
    out = x.copy()
    for delay, g in [(0.031,0.32),(0.047,0.26),(0.071,0.2),(0.097,0.15)]:
        d = int(delay*SR); echo = np.zeros_like(x); echo[d:] = x[:-d]*g; out += echo
    return out
L = reverb(L); Rr = reverb(Rr)

# fondu entrée/sortie + normalisation
fin, fout = int(0.5*SR), int(2.2*SR)
fade = np.ones(N); fade[:fin] = np.linspace(0,1,fin); fade[-fout:] = np.linspace(1,0,fout)**1.5
L *= fade; Rr *= fade
peak = max(np.max(np.abs(L)), np.max(np.abs(Rr))) or 1.0
L = (L/peak)*0.89; Rr = (Rr/peak)*0.89

stereo = np.stack([L, Rr], axis=1)
pcm = (stereo*32767).astype('<i2')

import wave
out = os.path.join(HERE, "music.wav")
with wave.open(out, 'wb') as w:
    w.setnchannels(2); w.setsampwidth(2); w.setframerate(SR)
    w.writeframes(pcm.tobytes())
print("OK", out, os.path.getsize(out)//1024, "Ko")
