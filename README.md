# OBiBa acknowledgments

If you are using OBiBa software, please cite our work in your code, websites, publications or reports.

"The work presented herein was made possible using the OBiBa suite (www.obiba.org), a  software suite developed by Maelstrom Research (www.maelstrom-research.org)"

agate-drupal8
=============

Drupal obiba_agate module for interacting with [Agate](https://github.com/obiba/agate) application:
* sign-up
* sign-in
* sign-out

Progress
=============

Working in progress
- [x] Settings Parameters
- [x] Authentication
- [x] Add/Update Drupal User on login
- [x] Logout Drupal User if no Agate Ticket
- [x] Single SingOn 
- [ ] Register User Page (Deal with Agate re-captcha)
    - [x] Form Registration
    - [x] User Added To Agate
    - [x] User saved in Drupal
    - [ ] Mail Activation process (new password creation form in Drupal)
    - [ ] Automatic Active user once password saved
    - [ ] Consolidate fields attribute creation in Agate (FirsName/LastName)
    - [ ] Realm field should be changed to select field and make it visible if multiple Agate Realm exist
- [ ] The Edit/Update Drupal user should update Agate User  
- [ ] Implement the Password reset process 
- [ ] Disable Drupal User if disabled/blocked in Agate (Optional)
- [ ] Delete Drupal User if deleted in Agate (Optional)
- [ ] Migration implementation from Agate Drupal7
- [ ] Implement Sql Database connect Realm
- [ ] Implement Ldap connect Realm
- [ ] Implement Open ID connect Realm
- [ ] Implement OAuth2 authentication
 
