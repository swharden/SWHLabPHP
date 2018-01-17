import os
import glob
import numpy as np
import datetime
from PIL import Image
from PIL import ImageDraw
from PIL import ImageFont
from PIL import ImageEnhance
from PIL import ImageOps
import scipy.ndimage
import sys

def image_convert(fname,saveAs=True,showToo=False):
    """
    Convert weird TIF files into web-friendly versions.
    Auto contrast is applied (saturating lower and upper 0.1%).
        make saveAs True to save as .TIF.png
        make saveAs False and it won't save at all
        make saveAs "someFile.jpg" to save it as a different path/format
    """

    # load the image
    im=scipy.ndimage.imread(fname) #scipy does better with it
    im=np.array(im,dtype=float) # now it's a numpy array

    # do all image enhancement here
    cutoffLow=np.percentile(im,.01)
    cutoffHigh=np.percentile(im,99.99)
    im[np.where(im<cutoffLow)]=cutoffLow
    im[np.where(im>cutoffHigh)]=cutoffHigh

    # IMAGE FORMATTING
    im-=np.min(im) #auto contrast
    im/=np.max(im) #normalize
    im*=255 #stretch contrast (8-bit)
    im = Image.fromarray(im)

    # IMAGE DRAWING
    msg="Filename: %s\n"%os.path.basename(fname)
    timestamp = datetime.datetime.fromtimestamp(os.path.getctime(fname))
    msg+="Created: %s\n"%timestamp.strftime('%Y-%m-%d %H:%M:%S')
    d = ImageDraw.Draw(im)
    fnt = ImageFont.truetype("arial.ttf", 20)
    d.text((6,6),msg,font=fnt,fill=0)
    d.text((4,4),msg,font=fnt,fill=255)

    if showToo:
        im.show()
    if saveAs is False:
        return
    if saveAs is True:
        saveAs=fname+".png"
    im.convert('RGB').save(saveAs)

def image_convert2(fname,saveAs=True):
    """
    backup for if the first doesn't work
    """
    im = Image.open(fname)
    im = im.convert('RGB')
    im = ImageOps.autocontrast(im,.05)
    im.save(saveAs)

def convert_tifs(path, overwrite=False):
    path=os.path.abspath(path)
    print("converting all TIFs in",path)
    for tifIn in sorted(glob.glob(path+"/*.tif")):
        tifIn=os.path.abspath(tifIn)
        folderIn=os.path.dirname(tifIn)
        folderOut=os.path.abspath(folderIn+"/swhlab/")
        tifOut=os.path.join(folderOut,os.path.basename(tifIn)+".jpg")
        if overwrite is False and os.path.exists(tifOut):
            print("skipping", os.path.basename(tifOut));
            continue
        else:
            print("converting", os.path.basename(tifOut));

            # SCIPY - good for most images
            try:
                image_convert(tifIn, tifOut)
                continue
            except:
                print(" METHOD 1 FAIL")

            # PIL - for what crashes
            try:
                image_convert2(tifIn, tifOut)
                continue
            except:
                print(" METHOD 2 FAIL")

            print(" ALL METHODS FAILED!")

    print("DONE")

if __name__=="__main__":
    if len(sys.argv)==1:
        print("DEVELOPER TESTING")
        convert_tifs(R"X:\Data\projects\2017-06-16 OT-Cre mice\data\2017-11-06 MT AP")
    elif len(sys.argv)==2 and os.path.exists(sys.argv[1]):
        convert_tifs(sys.argv[1])
    else:
        print("ARGUMENT ERROR")
        print('Usage: python convertImages.py "X:\path\to\stuff\"')
