import glob
import os
swhlabDir=r"C:\Users\scott\Documents\Data\swhlab"
for fname in glob.glob(swhlabDir+"/*.jpg"):
#    if len(os.path.basename(fname))==29:
#        abf=os.path.basename(fname).split("_")[0]+".abf"
#        abf=os.path.dirname(swhlabDir)+'/'+abf
#        print(abf)
#        if not os.path.exists(abf):        
#            f=open(abf,'w')
#            f.write("dummy")
#            f.close()    
#        f=open(abf.replace(".abf",".tif"),'w')
#        f.write("dummy")
#        f.close()
#            
    abfid=os.path.basename(fname).split("_")[0]
    abfPath=os.path.dirname(swhlabDir)+'/'+abfid+".abf"
    if not os.path.exists(abfPath):
        print(abfPath)  
        f=open(abfPath,'w')
        f.write("dummy")
        f.close()    