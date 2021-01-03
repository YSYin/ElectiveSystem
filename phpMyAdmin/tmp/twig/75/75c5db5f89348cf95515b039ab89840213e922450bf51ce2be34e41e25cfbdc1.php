<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* privileges/privileges_summary_row.twig */
class __TwigTemplate_4ccdda73a1887430ff15535592e9250fbb1a23c670eb1090b2a0c1f760442ca6 extends \Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 1
        echo "<tr>
    <td>";
        // line 2
        echo twig_escape_filter($this->env, ($context["name"] ?? null), "html", null, true);
        echo "</td>
    <td><code>";
        // line 3
        echo ($context["privileges"] ?? null);
        echo "</code></td>
    <td>";
        // line 4
        echo twig_escape_filter($this->env, ((($context["grant"] ?? null)) ? (_gettext("Yes")) : (_gettext("No"))), "html", null, true);
        echo "</td>

    ";
        // line 6
        if ((($context["type"] ?? null) == "database")) {
            // line 7
            echo "        <td>";
            echo twig_escape_filter($this->env, ((($context["table_privs"] ?? null)) ? (_gettext("Yes")) : (_gettext("No"))), "html", null, true);
            echo "</td>
    ";
        } elseif ((        // line 8
($context["type"] ?? null) == "table")) {
            // line 9
            echo "        <td>";
            echo twig_escape_filter($this->env, ((($context["column_privs"] ?? null)) ? (_gettext("Yes")) : (_gettext("No"))), "html", null, true);
            echo "</td>
    ";
        }
        // line 11
        echo "
    <td>";
        // line 12
        echo ($context["edit_link"] ?? null);
        echo "</td>
    <td>";
        // line 13
        echo ($context["revoke_link"] ?? null);
        echo "</td>
</tr>
";
    }

    public function getTemplateName()
    {
        return "privileges/privileges_summary_row.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  68 => 13,  64 => 12,  61 => 11,  55 => 9,  53 => 8,  48 => 7,  46 => 6,  41 => 4,  37 => 3,  33 => 2,  30 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "privileges/privileges_summary_row.twig", "E:\\PKU\\self-improvement\\work\\CSCode\\Codes\\Web\\ElectiveSystem\\phpMyAdmin\\templates\\privileges\\privileges_summary_row.twig");
    }
}
