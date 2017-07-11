"""
COMMAND MANAGER
* use PHP to append commands to the command text file
* call this script to run commands one by one, saving output as JSON
* call this script to clear or clean JSON output
"""

import sys
import subprocess
import time
import datetime
import json
import os

COMMANDS_TXT=os.path.abspath(os.path.join(os.path.dirname(__file__),"./COMMAND_LIST.txt"))
COMMANDS_LOG=os.path.abspath(os.path.join(os.path.dirname(__file__),"./COMMAND_LOG.json"))

def logClear():
    print("clearing log entirely")
    with open(COMMANDS_LOG,'w') as f:
        f.write(" ")
    return

def logClean(keep=3):
    with open(COMMANDS_LOG) as f:
        raw=f.read().split("}\n\n{")
    if len(raw)<=keep:
        print("nothing to clean")
        return
    print("keeping only %d most recent logs..."%keep)
    with open(COMMANDS_LOG,'w') as f:
        f.write("{"+"}\n\n{".join(raw[-keep:]))

def runCommand(cmd,logFile=COMMANDS_LOG):
    print("executing:",cmd)
    log={}
    log["command"]=cmd
    log["timeEpoch"]=time.time()
    log["timeStamp"]=datetime.datetime.now().strftime("%y%m%d %H:%M:%S")
    p = subprocess.run(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE, shell=True)
    log["msElapsed"]=(time.time()-log["timeEpoch"])*1000
    log["returnCode"]=p.returncode
    log["stdout"]=p.stdout.decode("utf-8")
    log["stderr"]=p.stderr.decode("utf-8")
    #j={"ID":log["timeEpoch"],"guts":log}
    j={str(log["timeEpoch"]):log}
    with open(COMMANDS_LOG,'a') as f:
        f.write("\n"+json.dumps(j,indent=4)+"\n")
    print("completed (return code %d) in %.02f ms\n"%(log["returnCode"],log["msElapsed"]))

def runNextCommand(cmdFile=COMMANDS_TXT):
    with open(COMMANDS_TXT) as f:
        raw=f.read().strip()
        if len(raw)<2:
            print("all commands complete.")
            return False
        raw=raw.split("\n")
    runCommand(raw[0])
    with open(COMMANDS_TXT,'w') as f:
        f.write("\n".join(raw[1:]))
    return True

if __name__=="__main__":
    if len(sys.argv)==1:
        print("### NO ARGUMENTS - YOU MUST BE DEVELOPING THIS ###")
    elif sys.argv[1]=="run1":
        runNextCommand()
    elif sys.argv[1]=="runAll":
        while runNextCommand():
            pass
    elif sys.argv[1]=="logClear":
        logClear()
    elif sys.argv[1]=="logClean":
        logClean(50)
    else:
        print("ERROR: I need a correct argument!")
