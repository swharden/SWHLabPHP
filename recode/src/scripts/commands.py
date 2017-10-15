import sys
import os
import time
import datetime
import traceback
import matplotlib.pyplot as plt
plt.ion()

PATH = os.path.dirname(os.path.abspath(__file__)) # this file
PATH_SWHLAB = os.path.abspath(PATH+"/../../../../../repos/swhlab/")
sys.path.insert(0,PATH_SWHLAB)
#sys.path.append(R"X:\Lab Documents\network\repos\swhlab")
import swhlab
import swhlab.analysis.protocols
#sys.path.insert(0,PATH+"/../../../repos/ROI-Analysis-Pipeline/pyLS")

CMDFILE = os.path.join(PATH,"commands.txt")
LOGFILE = os.path.join(PATH,"log.txt")

def getstamp():
    stamp = datetime.datetime.fromtimestamp(time.time())
    stamp = stamp.strftime('%Y-%m-%d %H:%M:%S')
    return stamp

def analyze(abfFile):
    print("analyzing",abfFile)
    swhlab.analysis.protocols.analyze(abfFile,show=False)
    print("SUCCESS\n")

def log(msg):
    line="[%s] %s\n"%(getstamp(),msg)
    print(line)
    with open(LOGFILE,'a') as f:
        f.write(line)
    return

def runTopCommand():
    with open(CMDFILE) as f:
        raw=f.read().strip()
    if len(raw)<3:
        print("no commands found")
        return
    raw=raw.split("\n")
    command=raw.pop(0) # pluck out the top command
    log(command)
    with open(CMDFILE,'w') as f:
        f.write("\n".join(raw))
    t1 = time.perf_counter()
    command=command.split(" ",1)
    if command[0]=="analyze":
        try:
            analyze(command[1])
        except Exception as e:
            e=traceback.format_exc()
            print("ERROR",e)
            log("ERROR: "+e)
    else:
        log("not sure how to run that command\n")
    elapsed = time.perf_counter()-t1
    log("completed [%.03f s]\n"%elapsed)
    return

if __name__=="__main__":
    #analyze(R"X:\Data\SCOTT\2017-10-10 aging BLA round2\2017_10_10_1007.abf")

    print("MONITORING",PATH)
    while True:
        print("[%s] checking"%getstamp())
        runTopCommand()
        time.sleep(1)