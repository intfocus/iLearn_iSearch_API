<?php
echo("hello php world!");
header("Content-Type:text/html;charset=utf-8");
$link=mysqli_connect("127.0.0.1","root","123456");
if(!$link) echo "FAILD!连接错误，用户名密码不对";
else echo "OK!可以连接"; 
phpinfo();
?>

<?php
  //$doc = new DOMDocument();
  //$doc->load( 'books.xml' );
  //$books = $doc->getElementsByTagName( "saml:book" );
  //foreach( $books as $book )
  //{
  //$authors = $book->getElementsByTagName( "author" );
  //$author = $authors->item(0)->nodeValue;
  
  //$publishers = $book->getElementsByTagName( "publisher" );
  //$publisher = $publishers->item(0)->nodeValue;
  
  //$titles = $book->getElementsByTagName( "title" );
  //$title = $titles->item(0)->nodeValue;
  
  //echo "$title - $author - $publisher\n";
  //}
// $xml = '
// <infos>
// <para><note>note1</note><extra>extra1</extra></para>
// <para><note>note2</note><extra>extra1</extra></para>
// <para><note>note3</note><extra>extra3</extra></para>
// </infos>
// ';
// 
// $p = xml_parser_create();
// xml_parse_into_struct($p, $xml, $values, $tags);
// xml_parser_free($p);
// $result = array();
// //下面的遍历方式有bug隐患
// for ($i=0; $i<3; $i++) {
  // $result[$i] = array();
  // $result[$i]["note"] = $values[$tags["NOTE"][$i]]["value"];
  // echo $values[$tags["EXTRA"][$i]] . "<br />";
  // //$result[$i]["extra"] = $values[$tags["EXTRA"][$i]]["value"];
// }
// //return;
// print_r($result);
?>