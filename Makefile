version=1.0-dev
branch=7.x-1.x


#
# Push to Drupal.org
#
git-push:
	sed -i "/^version/d" obiba_auth.info && \
	sed -i "/^project/d" obiba_auth.info && \
	sed -i "/^datestamp/d" obiba_auth.info && \
	$(call git-prepare) . && \
	rsync -av * target/obiba_auth --exclude=target --exclude=README.md  --exclude=Makefile && \
	$(call git-finish)

git-prepare = rm -rf target/obiba_auth && \
	mkdir -p target/obiba_auth && \
	echo "Enter Drupal username?" && \
	read git_username && \
	git clone --recursive --branch $(branch) $$git_username@git.drupal.org:project/obiba_auth.git target/obiba_auth && \
	cd target/obiba_auth && \
	git rm -rf * && \
	cd ../.. && \

git-finish = cd target/obiba_auth && \
	git add . && \
	git status && \
	echo "Enter a message for this commit?" && \
	read git_commit_msg && \
	git commit -m "$$git_commit_msg" && \
	git push origin $(branch)
