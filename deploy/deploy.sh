#!/usr/bin/env bash

echo "Starting deploy to WordPress.org SVN"

# 1. Clone complete SVN repository to separate directory
svn co $SVN_REPOSITORY ./svn

# 2. Copy git repository contents to SNV trunk/ directory
rsync \
  --exclude svn \
  --exclude assets \
  --exclude deploy \
  --exclude .git \
  --exclude .travis.yml \
  -vaz ./* ./svn/trunk/
rsync -vaz assets/ ./svn/

# 3. Switch to SVN repository
cd svn

# 4. Create SVN tag
svn cp \
  trunk tags/$TRAVIS_TAG \
  --username $SVN_USERNAME \
  --password $SVN_PASSWORD

# 5. Push SVN tag
svn ci \
  --message "Release $TRAVIS_TAG" \
  --username $SVN_USERNAME \
  --password $SVN_PASSWORD \
  --non-interactive
