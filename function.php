<?php
require_once('dom.php');
function rex_parse_autoresponderee($mycode)
    {
        /* 1. check if the code is from Mailchimp
         * 2. Check if the code is from getresponse, which has hidden input fields (type=text_
         * 3. Parse the form to get inputs type="text"
         * 4. Parse the form to get inputs type="checkbox"
         * 5. Parse the form to get inputs type="radio"
         * 6. Parse the form to get textarea
         * 7. Parse the form to get inputs type="submit"/button
         * 8. Parse the form to get select boxes
         *
         */

        //remove the hidden box from mailchimp
       $pattern = '/<div style="position: absolute; left: -5000px;">.*?div\>/s';
        $code = preg_replace($pattern, '', $mycode);

        //remove checking fields in mailpoet
        if (stripos($code, "wysija"))
        {
            $pattern = '/<span class="abs-req">.*?span\>/s';
            $code = preg_replace($pattern, '', $code);

        }


        $form_object = rex_str_get_dom($code);
        $input_text = array();
        $input_radio = array();
        $input_checkbox = array();
        $submit = array();
        $input_hidden = array();
        $textarea = array();
        $select = array();

        $i = 0;
        $all = $form_object->select("input, button, textarea");

        foreach ($all as $a)
        {
           // var_dump($a);
            $a->elem_order = $i;
            $i++;
        }



        $form = $form_object->select("form");

        $form_array = array();

        foreach ($form as $f)
        {
            $form_array["action"] = $f->action;
            $form_array["method"]   = $f->method;
        }

        //get all input text
        $object = $form_object->select("input[type=text], input[type=email], input[type=number], input[type=search], input[type=color]");


        foreach($object as $o)
        {
            if ($o->value == "")
            {
                $o->value = $o->name;
            }

            $tag = "<input elem-order='$o->elem_order' type='$o->type' value='$o->value' name='$o->name' placeholder='$o->placeholder' required='$o->required' />";
            $input_text[] = $tag;

        }

        //get all input hidden
        $object = $form_object->select("input[type=hidden]");

        foreach($object as $o)
        {
            $input_hidden[]       = $o->html();
        }
        //get all input radio
        $object = $form_object->select("input[type=radio]");

        foreach($object as $o)
        {
            $tag = "<li><input elem-order='$o->elem_order' type='$o->type' value='$o->value' name='$o->name' checked='$o->checked' placeholder='$o->placeholder' required='$o->required' /> $o->value</li>";
            $input_radio[] = $tag;
        }

        //get all input checkbox
        $object = $form_object->select("input[type=checkbox]");

        foreach($object as $o)
        {
            $tag = "<li><input elem-order='$o->elem_order' type='$o->type' value='$o->value' name='$o->name' checked='$o->checked' placeholder='$o->placeholder' required='$o->required' /> $o->value</li>";
            $input_checkbox[] = $tag;
        }

        //get all select
        $object = $form_object->select("select");

        foreach($object as $o)
        {
            $select[] = $o->html();

        }

        //get all textarea
        $object = $form_object->select("textarea");

        foreach($object as $o)
        {
            $tag = "<textarea elem-order='$o->elem_order' name='$o->name' required='$o->required' maxlength='$o->maxlength' placeholder='$o->placeholder'>$o->getInnerText()</textarea>";
            $textarea[] = $tag;
        }

        //get submit button

        $object = $form_object->select("input[type=submit], input[type=image], input[type=button]");

        foreach($object as $o)
        {

            if ($o->tag == "button")
            {
                $tag = "<input elem-order='$o->elem_order' type='submit' name='$o->name' value='$o->getInnerText()'  />";
            } else
            {
                $tag = "<input elem-order='$o->elem_order' type='submit' name='$o->name' value='$o->value'  />";
            }

            $submit[] = $tag;
        }

        //in case there is no submit button (use link and js to submit form)
        if (count($submit) == 0)
        {
            $submit[] = "<input elem-order='100' type='submit' name='Submit' value='Submit'  />";
        }

        if (count($input_radio) > 0)
        {
            $input_radio = "<ul>".implode("",$input_radio)."</ul>";
        } else
        {
            $input_radio = "";
        }

        if (count($input_checkbox) > 0)
        {
            $input_checkbox = "<ul>".implode("",$input_checkbox)."</ul>";
        } else
        {
            $input_checkbox = "";
        }
        $return_array = array(
            "input_text"    => implode("",$input_text),
            "input_hidden"  => implode("",$input_hidden),
            "input_radio"   => $input_radio,
            "input_checkbox"    => $input_checkbox,
            "textarea"          => implode("",$textarea),
            "select"            => implode("",$select),
            "form"              => $form_array,
            "submit"            => implode("",$submit)

        );
        /*$return_array_withme  = "meisrex".json_encode($return_array)."meisrexagain";*/
        
        return $return_array;
        
    }
$finalautorescode = rex_parse_autoresponderee($finalautorescode);

?>

<form action="<?php echo $finalautorescode['form']['action']; ?>" method="<?php echo $finalautorescode['form']['method']; ?>" >
<?php echo $finalautorescode['input_hidden'] ;?>
	<?php echo $finalautorescode['input_text'] ;?>
	<?php echo $finalautorescode['input_radio'] ;?>
	<?php echo $finalautorescode['input_checkbox'] ;?>
	<?php echo $finalautorescode['select'] ;?>
	<?php echo $finalautorescode['textarea'] ;?>
	<?php echo $finalautorescode['submit'] ;?>
</form>

