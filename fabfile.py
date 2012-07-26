
from fabric.api import *

files = ['local/soda2',
         'lib/jeelo_access.php',
         'mod/jeelo',
         'course/format/jeelo']

def update():
    collect()
    local('git pull')
    for path in files:
        local('cp -rf package/%s public_html/%s' % (path, path))

def pack():
    collect()
    local('tar -cz package > package.tar.gz')
    local('tar -cz public_html > public_html.tar.gz')

def collect():
    for path in files:
        local('cp -rf public_html/%s package/%s' % (path, path)) 
