
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

def pack(collect_all=True):
    if collect_all:
        collect()
    local('tar -cz package > package.tar.gz')
    local('tar -cz public_html > public_html.tar.gz')

def collect():
    local('rm -rf package/*')
    local('mkdir package/lib')

    for path in files:
        if not '.' in path:
            local('mkdir -p package/%s' % path)

        local('cp -rf public_html/%s package/%s' % (path,
                                                    #'/' if not '.' in path else '',
                                                    '/'.join(path.split('/')[0:-1])))

def push(message="Updating package", collect_all=True):
    if collect_all:
        pack()
    local("git commit -a -m '%s'" % message)
    local("git push")
