<?php
 //������ ���� ��������
 include('config.php');
 include($inc_path.'/service/class.pager.php');
 unset($main);
 $FILENAME = $front_html_path.'front.html';

 $main = &addInCurrentSection($FILENAME);
 $where = ' ';
 if (isset($_GET['word'])) {

     $word= htmlspecialchars ( addslashes (urldecode($_GET['word'])));
     $word=trim(preg_replace('/\s+/', ' ', $word));
     $words= explode(' ', $word);
     $fields = array('fio','about');

     foreach ( $fields as $f) {
         foreach ( $words as $sw ) {
            $where .= "or $f LIKE '%$sw%'";
         };
     };

     $where = ' and ('.substr($where, 3).')';
     $main->addField('word',$word);
     $link="/word/".urlencode($word);
 };

//var_dump($_POST);

 if ($_COOKIE['id_city']>0) $where.=' and id_city ='.$_COOKIE['id_city'];

 $id=''; $razdel='';
 foreach ($_POST as $key =>$value) {
    if (strpos($key,'razdel_sub') >= 0 ){
       $id .= ','.substr($key,10);
    };
 };
 if ($_GET['razdel'] > 0 )
       $razdel = " and id_type = $_GET[razdel]";

 if ($id !=='') {
         $id=substr($id,1);
         $razdel=" and id_type in ($id)";
 };

 $where  .= $razdel;
 //echo $where;
 $query = "select distinct(id), z.* from users z left join user_types zt on  z.id = zt.id_user where z.is_master=1 $where order by date desc";

//echo $query;
 if ($pg = Pagers::DA($db,'','', $GLOBALS[$modulName.'_fcount'],$_GET['cp'],'/'.$site->pageid.$link,null,null,$query)) {   $pg->addPAGER($main);
   $r = &$pg->r;

   if ($_GET['word']!=='' && isset($_GET['word'])) {
         $message = "�� ������ ������� ������� �������: ".$r->num_rows();
         if ($message!=='') $main->addField('message',$message);
   };
   $i=1;
   while ($r->next_row()) {

        unset($sub);
        $sub = new outTree();
        $r->addFields($sub,$ar=array('id','fio','is_free','about','watch'));
        add_user_avatar($sub,$r);
        if ($r->result('is_free') >0) { $sub->addfield('status','�����');  $sub->addfield('bisy','_bisy');}
             else $sub->addfield('status','��������');
        if ($r->result('price') >0) {
                $r1 = new Select($db,"select * from time where id=".$r->result('time'));
                $r1->next_row();
                $sub->addfield('price','�� '.$r->result('price').' ���./'.$r1->result('name'));
         }    else  $sub->addfield('price','���� ����������');
         if ($r->result('id_city')>0) {
            $r1 = new Select($db,"select * from city where id=".$r->result('id_city'));
            $r1->next_row();
            $sub->addfield('city',$r1->result('name'));
         };
         $sub->addfield('cnt_review',cnt_review($r->result('id'),2));

         //�������
         if ($i==2) addreklama ($sub,$razdel,'master',$_COOKIE['id_city']);

         add_user_types($sub,$r->result('id'));
         add_star($sub,$r->result('id'),2);
         $main->addField('sub',&$sub);
         $i++;
   };
  } else  $main->addField('no_sub','');


 ?>


