"""Imposition livret A4 paysage (2 pages A5 par face, pliage au centre, agrafage)."""
import sys, warnings; warnings.filterwarnings('ignore')
from pypdf import PdfReader, PdfWriter, Transformation, PageObject
for t in (10,20,50):
    r=PdfReader(f'Formation-Ambassadeur-{t}-livre.pdf'); n=len(r.pages)
    order=[]; a,b=1,n
    while a<b:
        order += [(b,a),(a+1,b-1)]; a+=2; b-=2
    w=float(r.pages[0].mediabox.width); h=float(r.pages[0].mediabox.height)
    out=PdfWriter()
    for left,right in order:
        sheet=PageObject.create_blank_page(width=w*2, height=h)
        for idx,dx in ((left,0),(right,w)):
            p=r.pages[idx-1]
            sheet.merge_transformed_page(p, Transformation().translate(dx,0))
        out.add_page(sheet)
    f=f'Formation-Ambassadeur-{t}-livret-A4.pdf'
    with open(f,'wb') as fh: out.write(fh)
    print(f, len(out.pages), 'feuilles-faces', f'({w:.0f}x{h:.0f} -> {w*2:.0f}x{h:.0f})')
