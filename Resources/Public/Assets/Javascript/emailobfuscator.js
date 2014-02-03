/**
 * emailobfuscator
 *
 */

var cfe = '>';
var ecf = '</';
var cef = 'a';


function removeNoScriptHTML() {
    var el = document.getElementsByClassName('tx-emailobfuscator-noscript');
    for (var i = 0; i != el.length; i++) {
        el[i].style.display = 'none';
    }
}
window.onload = removeNoScriptHTML;


