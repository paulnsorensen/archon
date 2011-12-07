#! /usr/bin/python

# To change this template, choose Tools | Templates
# and open the template in the editor.

__author__ = "paul"
__date__ = "$Apr 26, 2011 12:44:41 PM$"

import json

import getpass
import os
import urllib
import urllib2

if __name__ == "__main__":

   # build opener with HTTPCookieProcessor
   o = urllib2.build_opener(urllib2.HTTPCookieProcessor())

   urllib2.install_opener(o)

   archon_url = raw_input("Archon URL (e.g. http://sandbox.archon.org/latest/): ")

   username = raw_input("Username: ")
   password = getpass.getpass()

   p = urllib.urlencode({'ArchonLogin': username, 'ArchonPassword': password})


   # perform login with params
   try:
      f = o.open(archon_url, p)
      data = f.read()
      f.close()
   except:
      print "Invalid or unavailable URL"
      exit(1)

   # get session info
   try:
      f = o.open(archon_url + '?p=core/account&f=sessioninfo')
      data = f.read()
      f.close()
      sessioninfo = json.loads(data)
   except:
      print "Invalid or unavailable URL"
      exit(1)

   if not sessioninfo['authenticated']:
      print "Authentication failed"
      exit(1)

   if not sessioninfo['administrativeaccess']:
      print "Use does not have administrative access"
      exit(1)


   f = o.open(archon_url + '?p=admin/digitallibrary/digitallibrary&f=getfilelist')
   data = f.read()
   f.close()

   filelist = json.loads(data)

   if len(filelist) == 0:
      exit(0)

   base_dir = 'archon_fileexport'
   dir = base_dir
   a = 1
   while os.path.exists(dir):
      dir = base_dir + ' (' + str(a) + ')'
      a += 1
   os.makedirs(dir)

   print "Directory created, retrieving files..."

   for i in filelist:
      url = archon_url + '?p=digitallibrary/getfile&id=' + str(i['id'])

      f = o.open(url);
      data = f.read();
      f.close()

      path = dir + '/' + str(i['dcid'])
      if not os.path.exists(path):
         os.makedirs(path)

      y = open(path + '/' + i['filename'], 'w')
      y.write(data)

   print "Successfully exported digital library files!"