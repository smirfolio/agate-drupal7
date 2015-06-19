version=1.0-dev
branch=7.x-1.x
mica_client_folder=projects/mica-drupal7-client

#
# dev-deploy
#
#
dev-deploy:
	echo $(mica_client_folder) && \
	rm -rf ~/$(mica_client_folder)/target/drupal/sites/all/modules/obiba_agate && \
	ln -s  $(CURDIR) ~/$(mica_client_folder)/target/drupal/sites/all/modules/obiba_agate

#
# Push to Drupal.org
#
git-push:
	sed -i "/^version/d" obiba_agate.info && \
	sed -i "/^project/d" obiba_agate.info && \
	sed -i "/^datestamp/d" obiba_agate.info && \
	$(call git-prepare) . && \
	rsync -av * target/obiba_agate --exclude=target --exclude=README.md  --exclude=Makefile && \
	$(call git-finish)

git-prepare = rm -rf target/obiba_agate && \
	mkdir -p target/obiba_agate && \
	echo "Enter Drupal username?" && \
	read git_username && \
	git clone --recursive --branch $(branch) $$git_username@git.drupal.org:project/obiba_agate.git target/obiba_agate && \
	cd target/obiba_agate && \
	git rm -rf * && \
	cd ../.. && \

git-finish = cd target/obiba_agate && \
	git add . && \
	git status && \
	echo "Enter a message for this commit?" && \
	read git_commit_msg && \
	git commit -m "$$git_commit_msg" && \
	git push origin $(branch)
