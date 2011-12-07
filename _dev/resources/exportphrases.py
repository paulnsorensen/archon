#! /usr/bin/python

__author__ = "Paul Sorensen"


import getpass
import os
import urllib
import urllib2
import xml.dom.minidom

if __name__ == "__main__":

   langs = [
      'eng',
      'spa'
   ]

   aprcodes = [
      'accessions',
      'avsap',
      'collections',
      'core',
      'creators',
      'digitallibrary',
      'subjects'
   ]

# build opener with HTTPCookieProcessor
o = urllib2.build_opener(urllib2.HTTPCookieProcessor())

urllib2.install_opener(o)

username = raw_input("Username:")
password = getpass.getpass()

p = urllib.urlencode({'ArchonLogin': username, 'ArchonPassword': password})


# perform login with params
f = o.open('http://localhost/paul/', p)
data = f.read()
f.close()


for l in langs:
   for a in aprcodes:

      url = 'http://localhost/paul/?p=admin/core/database&f=export&exportutility=core/phrasexml&language=' + l + '&aprcode=' + a

      f = o.open(url);
      data = f.read();
      f.close()
      try:
         z = xml.dom.minidom.parseString(data)

         path = '../../packages/' + a + '/install/phrasexml/' + l + '-' + a + '.xml'

         if os.path.exists(path):
            y = open(path, 'w')
            y.write(data)

      except:
         print "document invalid for aprcode: " + a + " language: " + l