#! /usr/local/bin/python

__author__="Paul Sorensen"


import sys
import fnmatch
import os
import shutil

if __name__ == "__main__":

   if os.path.exists('.bzr'):
      print "Bazaar directory exists. Please use bzr export first."
      sys.exit(1)
   else:
      dirs = [
         '_dev',
         'packages/collections/templates/ala',
         'themes/aall',
         'themes/ala',
         'themes/avsap',
         'themes/ihlc',
         'themes/sousa'
         ]

      print "\nRemoving extraneous directories..."
      for d in dirs:
         if os.path.exists(d):
            shutil.rmtree(d)
         else:
            print "\tPath '" + d + "' does not exist."

      print "\nRenaming core install file..."
      if os.path.isfile('packages/core/install/install.php_'):
         os.rename('packages/core/install/install.php_', 'packages/core/install/install.php')

      print "\nRenaming blank config file..."
      if not os.path.isfile('config.inc.php'):
         os.rename('configblank.inc.php', 'config.inc.php')
      else:
         print "\nWarning! config.inc.php already exists."

      # find files with ".old" in name and remove those
      print "\nSearching for files with '.old' in name..."
      for (path, dirs, files) in os.walk(os.getcwd()):
         for f in files:
            if fnmatch.fnmatch(f, '*.old*'):
               print "\tDeleting file:" + path + "/" + f
               os.remove(path + "/" + f)

      if os.path.isfile('internal_release.py'):
         os.remove('internal_release.py')

      print "\nDone!\n"