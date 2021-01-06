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

/* privileges/column_privileges.twig */
class __TwigTemplate_3ada2512de6d3a1c66676a2e0e5794f24ae835438282c540ec01f4d5fb77ee4b extends \Twig\Template
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
        echo "<div class=\"item\" id=\"div_item_";
        echo twig_escape_filter($this->env, ($context["name"] ?? null), "html", null, true);
        echo "\">
    <label for=\"select_";
        // line 2
        echo twig_escape_filter($this->env, ($context["name"] ?? null), "html", null, true);
        echo "_priv\">
        <code><dfn title=\"";
        // line 3
        echo twig_escape_filter($this->env, ($context["name_for_dfn"] ?? null), "html", null, true);
        echo "\">";
        echo twig_escape_filter($this->env, ($context["priv_for_header"] ?? null), "html", null, true);
        echo "</dfn></code>
    </label>

    <select id=\"select_";
        // line 6
        echo twig_escape_filter($this->env, ($context["name"] ?? null), "html", null, true);
        echo "_priv\" name=\"";
        echo twig_escape_filter($this->env, ($context["name_for_select"] ?? null), "html", null, true);
        echo "[]\" multiple=\"multiple\" size=\"8\">
        ";
        // line 7
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["columns"] ?? null));
        foreach ($context['_seq'] as $context["curr_col"] => $context["curr_col_privs"]) {
            // line 8
            echo "            <option value=\"";
            echo twig_escape_filter($this->env, $context["curr_col"], "html", null, true);
            echo "\"
            ";
            // line 9
            if ((($this->getAttribute(($context["row"] ?? null), ($context["name_for_select"] ?? null), [], "array") == "Y") || $this->getAttribute($context["curr_col_privs"], ($context["name_for_current"] ?? null), [], "array"))) {
                // line 10
                echo "                selected=\"selected\"
            ";
            }
            // line 11
            echo ">
                ";
            // line 12
            echo twig_escape_filter($this->env, $context["curr_col"], "html", null, true);
            echo "
            </option>
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['curr_col'], $context['curr_col_privs'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 15
        echo "    </select>

    <em>";
        // line 17
        echo _gettext("Or");
        echo "</em>
    <label for=\"checkbox_";
        // line 18
        echo twig_escape_filter($this->env, ($context["name_for_select"] ?? null), "html", null, true);
        echo "_none\">
        <input type=\"checkbox\" name=\"";
        // line 19
        echo twig_escape_filter($this->env, ($context["name_for_select"] ?? null), "html", null, true);
        echo "_none\"
            id=\"checkbox_";
        // line 20
        echo twig_escape_filter($this->env, ($context["name_for_select"] ?? null), "html", null, true);
        echo "_none\"
            title=\"";
        // line 21
        echo _pgettext(        "None privileges", "None");
        echo "\" />
            ";
        // line 22
        echo _pgettext(        "None privileges", "None");
        // line 23
        echo "    </label>
</div>
";
    }

    public function getTemplateName()
    {
        return "privileges/column_privileges.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  106 => 23,  104 => 22,  100 => 21,  96 => 20,  92 => 19,  88 => 18,  84 => 17,  80 => 15,  71 => 12,  68 => 11,  64 => 10,  62 => 9,  57 => 8,  53 => 7,  47 => 6,  39 => 3,  35 => 2,  30 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "privileges/column_privileges.twig", "E:\\PKU\\self-improvement\\work\\CSCode\\Codes\\Web\\ElectiveSystem\\phpMyAdmin\\templates\\privileges\\column_privileges.twig");
    }
}
