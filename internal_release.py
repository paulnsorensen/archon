#! /usr/local/bin/python

__author__="Paul Sorensen"


import sys
import fnmatch
import os
import shutil
import fileinput

if __name__ == "__main__":

   repositories = [
      'ua',
      'ala',
      'ihlc',
      'rbml',
      'sandbox'
   ]

   if len(sys.argv) != 2:
      print "Argument required. Please specify which repository for which you are releasing ("+ ", ".join(repositories)+")."
      sys.exit(1)
   elif sys.argv[1] not in repositories:
      print "Invalid argument ("+ ", ".join(repositories)+")."
      sys.exit(1)
      
   if os.path.exists('.bzr'):
      print "Bazaar directory exists. Please use bzr export first."
      sys.exit(1)
   else:
      if sys.argv[1] == 'ua':               
         dirs = [
            '_dev',
            'packages/collections/templates/ala',
            'themes/aall',
            'themes/ala',
            'themes/avsap',
            'themes/ihlc',
         ]
      elif sys.argv[1] == 'ala':
         dirs = [
            '_dev',
            'packages/collections/templates/illinois',
            'packages/digitallibrary/templates/illinois',
            'themes/avsap',
            'themes/ihlc',
            'themes/illinois',
            'themes/sousa'
         ]
      elif sys.argv[1] == 'ihlc':
         dirs = [
            '_dev',
            'packages/collections/templates/ala',
            'packages/collections/templates/illinois',
            'packages/digitallibrary/templates/illinois',
            'themes/aall',
            'themes/ala',
            'themes/avsap',            
            'themes/illinois',
            'themes/sousa'
            ]
      elif sys.argv[1] == 'rbml':
         dirs = [
            '_dev',
            'packages/collections/templates/ala',
            'packages/collections/templates/illinois',
            'packages/digitallibrary/templates/illinois',
            'themes/aall',
            'themes/ala',
            'themes/avsap',   
            'themes/ihlc',
            'themes/illinois',
            'themes/sousa'
         ]
      elif sys.argv[1] == 'sandbox':
         dirs = [
            '_dev',
            'packages/collections/templates/ala',
            'packages/collections/templates/illinois',
            'packages/digitallibrary/templates/illinois',
            'themes/aall',
            'themes/ala',
            'themes/avsap',            
            'themes/ihlc',
            'themes/sousa'
         ]
         
         include_comment_str = "// ini_set('include_path', '.:' . get_include_path());"
         include_str = "ini_set('include_path', '/home3/chrispro/php:' . get_include_path());"
            
         for line in fileinput.input("includes.inc.php",inplace=1):
            if include_comment_str in line:
               line=line.replace(include_comment_str, include_str)
            print line,
            
         login_form_str = "<form action=\"<?php echo(encode($_SERVER['REQUEST_URI'], ENCODE_HTML)); ?>\" accept-charset=\"UTF-8\" method=\"post\">"   
         reset_guest_str = "  <div style='margin-bottom:2em'>Login using 'guest' as both the login and the password.  If you cannot login, click <a href='resetguest.php' style='font-weight:bold'>here</a> to reset the guest password and try again.</div>"
            
         for line in fileinput.input("themes/default/footer.inc.php",inplace=1):
            if login_form_str in line:
               line=line.replace(login_form_str, login_form_str+reset_guest_str)
            print line,
            
         shutil.move("_dev/resources/resetguest.php", "resetguest.php")
            
      else:
         sys.exit(1)
             
      print "\nRemoving extraneous directories..."
      for d in dirs:
         if os.path.exists(d):
            shutil.rmtree(d)
         else:
            print "\tPath '" + d + "' does not exist."

      if os.path.isfile('packages/core/install/install.php'):
         os.rename('packages/core/install/install.php', 'packages/core/install/install.php_')

      print "\nRemoving blank config file..."
      if os.path.isfile('config.inc.php'):
         os.remove('config.inc.php')
      
      if os.path.isfile('configblank.inc.php'):
         os.remove('configblank.inc.php')

      # find files with ".old" in name and remove those
      print "\nSearching for files with '.old' in name..."
      for (path, dirs, files) in os.walk(os.getcwd()):
         for f in files:
            if fnmatch.fnmatch(f, '*.old*'):
               print "\tDeleting file:" + path + "/" + f
               os.remove(path + "/" + f)

      print "\nDone!\n"