<?PHP

  function GetUserIfInGroup ($username, $ldapgroupdn) {
    $ldapserver = 'DC1.jasonwindows.local DC2.jasonwindows.local';
    $ldapbinduser = 'CN=jwsms,OU=Service Accounts,OU=System Users,OU=Users,OU=Accounts,DC=jasonwindows,DC=local';
    $ldapbindpass = 'ri-WH5wvPZf6tLM+RTE^VMj-Us3';
    $ldapusertree = "OU=Users,OU=Accounts,DC=jasonwindows,DC=local";
    $ldapuserattr = array ("displayname", "givenName", "mail");

    $ldapuserfilter = "(&(sAMAccountName=".$username.")(memberOf:1.2.840.113556.1.4.1941:=".$ldapgroupdn."))";
    $ldapconn = ldap_connect($ldapserver) or die("Could not connect to LDAP server.");

    $ldapbind = ldap_bind($ldapconn, $ldapbinduser, $ldapbindpass) or die ("Error trying to bind: ".ldap_error($ldapconn));

    $ldapresult = ldap_search($ldapconn, $ldapusertree, $ldapuserfilter, $ldapuserattr);
    $users = ldap_get_entries($ldapconn, $ldapresult);

    if ( $users["count"] == 1 ) {
      $user = array(
                    "displayname"=>$users[0]["displayname"][0],
                    "givenname"=>$users[0]["givenname"][0],
                    "mail"=>$users[0]["mail"][0]
                   );
    } else {
      $user = false;
    }

    ldap_close($ldapconn);

    return $user;
  }
?>
