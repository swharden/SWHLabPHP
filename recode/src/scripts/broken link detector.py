
"""
This script downloads the latest copy of the lab website, 
identifies all URLs, assesses each for 404 errors, then 
creates a new webpage (broken.html) where good links are
green and broken links are red.
"""

import time
import urllib.request

URL_INDEX="http://192.168.1.9"
PATH_URL=R"X:\Lab Documents\network\htdocs"
PATH_BROKEN_RAW=PATH_URL+"/broken.txt"
PATH_BROKEN=PATH_URL+"/broken.html"
STYLE_GOOD="background-color: lightgreen;"
STYLE_BROKEN="background-color: red;"
TOP_MESSAGE=Rf"""
<div style='padding: 100px; font-size: 200%; color: red; font-family: monospace;'>
BROKEN LINK DETECTION - PERFORMED ON {time.strftime("%m/%d/%Y %H:%M:%S")}<br>
X:\Lab Documents\network\htdocs\SWHLabPHP\recode\src\scripts\broken link detector.py
</div>
"""

def downloadLabWebsiteAsHTML():
    print(f"Downloading: {URL_INDEX} ...")
    html = urllib.request.urlopen(URL_INDEX).read()
    with open(PATH_BROKEN_RAW, 'wb') as f:
        f.write(html)
        print("Created:", PATH_BROKEN_RAW)

def highlightBadURLs():
    with open(PATH_BROKEN_RAW) as f:
        html = f.read()
    html = html.replace("\n", "")
    html = html.replace("<a", "\n<a")
    lines = html.split("\n")
    for i, line in enumerate(lines):
        print(f"checking URL {i+1} of {len(lines)}...")
        htmlLink, htmlAfter = line.replace("'", '"').split(">", 1)
        if not "href" in htmlLink:
            continue
        url = htmlLink.split("href")[1].split('"')[1]
        if url.startswith("onenote:"):
            continue
        if not url.startswith("http"):
            url = URL_INDEX+"/"+url
        if not URL_INDEX in url:
            continue
        thisUrlStyle = STYLE_GOOD
        try:
            urllib.request.urlopen(url)
        except:
            thisUrlStyle = STYLE_BROKEN
            print("bad URL:", url)
        lines[i] = f"<a style='{thisUrlStyle}' href='{url}'>{htmlAfter}"
    html = "\n".join(lines)
    html = html.replace('<body>', '<body>'+TOP_MESSAGE)
    with open(PATH_BROKEN, 'w') as f:
        f.write(html)
        print("Created:", PATH_BROKEN)


if __name__ == "__main__":
    downloadLabWebsiteAsHTML()
    highlightBadURLs()
    print("DONE")