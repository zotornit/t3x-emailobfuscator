### Testing the app functionality


# TYPOSCRIPT

Need to be logged in as admin. Refresh the page. check source code for changes.

```
# Default PAGE object:
page = PAGE
page.10 = TEXT
page.10.value = oberfuscate@only.once <br><br> <a href="mailt:oberfuscate@only.once">oberfuscate@only.once</a> <br><br>
config.contentObjectExceptionHandler = 0

  
page.20 = COA_INT
page.20 {
    10 = TEXT
    10.value = TEST oberfuscate@each.reload TEST <br><br> <a href="mailt:oberfuscate@each.reload">oberfuscate@each.reload</a> - this should be obfusctaed every time
}


plugin.tx_emailobfuscator.settings {
    enabled = 1
}



```
