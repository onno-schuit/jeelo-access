
from fabric.api import *

files = ['local/soda2',
         'lib/jeelo_access.php',
         'mod/jeelo',
         'course/format/jeelo']

def pack():
    collect()
    local('tar -cz package > package.tar.gz')
    local('tar -cz public_html > public_html.tar.gz')

def collect():
    for path in files:
        local('cp -rfu public_html/%s package/%s' % (path, path)) 
