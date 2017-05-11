<?php

/**
 * A dropdown field using Chosen
 *
 * @author LeKoala
 */
class ChosenField extends ListboxField
{
    protected $no_results_text;
    protected $allow_single_deselect = true;
    protected $allow_max_selected;
    protected $use_order = false;
    protected $disable_search = null;

    private static $throw_exceptions = false;

    public function __construct($name, $title = null, $source = array(), $value = '', $form = null, $emptyString = null)
    {
        parent::__construct($name, $title, $source, $value, $form, $emptyString);

        $this->setDefaultText(_t('ChosenField.DEFAULT_TEXT', 'Please select'));
        $this->no_results_text = _t('ChosenField.NO_RESULTS', 'Oops, nothing found!');
        $this->disabledItems = self::config()->disabled_items;
    }

    public static function Requirements($order = false)
    {
        // Use updated version of Chosen
        Requirements::block(FRAMEWORK_ADMIN_DIR . '/thirdparty/chosen/chosen/chosen.css');
        Requirements::block(FRAMEWORK_ADMIN_DIR . '/thirdparty/chosen/chosen/chosen.jquery.js');
        Requirements::css(CHOSENFIELD_DIR . '/javascript/chosen/chosen.min.css');
        Requirements::javascript(CHOSENFIELD_DIR . '/javascript/chosen/chosen.jquery.js');

        if (self::config()->use_bootstrap) {
            Requirements::css(CHOSENFIELD_DIR . '/javascript/bootstrap-chosen/bootstrap-chosen.css');
        }

        if ($order) {
            Requirements::javascript(CHOSENFIELD_DIR . '/javascript/chosen-order/chosen.order.jquery.min.js');
        }
    }

    public function Field($properties = array())
    {
        self::Requirements($this->use_order);

        $opts = array(
            'no_results_text' => $this->no_results_text,
            'allow_single_deselect' => $this->allow_single_deselect ? true : false
        );
        if (self::config()->rtl) {
            $this->addExtraClass('chosen-rtl');
        }
        if ($this->allow_max_selected) {
            $opts['allow_max_selected'] = $this->allow_max_selected;
        }
        if ($this->disable_search !== null) {
            $opts['disable_search'] = $this->disable_search;
        }
        if ($this->use_order) {
            $stringValue = $this->value;
            if (is_array($stringValue)) {
                $stringValue = implode(',', $stringValue);
            }
            $this->setAttribute('data-chosen-order', $stringValue);
        }
        $this->setAttribute('data-chosen', json_encode($opts));
        Requirements::javascript(CHOSENFIELD_DIR . '/javascript/ChosenField.js');
        return parent::Field($properties);
    }

    public function setValue($val, $obj = null)
    {
        // If we're not passed a value directly,
        // we can look for it in a relation method on the object passed as a second arg
        if (!$val && $obj && $obj instanceof DataObject && $obj->hasMethod($this->name)) {
            $funcName = $this->name;
            $val = array_values($obj->$funcName()->getIDList());
        }

        if ($val) {
            if (!$this->multiple && is_array($val)) {
                throw new InvalidArgumentException('Array values are not allowed (when multiple=false).');
            }

            if ($this->multiple) {
                $parts = (is_array($val)) ? $val : preg_split("/ *, */", trim($val));
                if (ArrayLib::is_associative($parts)) {
                    // This is due to the possibility of accidentally passing an array of values (as keys) and titles (as values) when only the keys were intended to be saved.
                    throw new InvalidArgumentException('Associative arrays are not allowed as values (when multiple=true), only indexed arrays.');
                }

                // Doesn't check against unknown values in order to allow for less rigid data handling.
                // They're silently ignored and overwritten the next time the field is saved.
                parent::setValue($parts);
            } else {
                if (!in_array($val, array_keys($this->getSource()))) {
                    if (self::config()->throw_exceptions) {
                        throw new InvalidArgumentException(sprintf('Invalid value "%s" for multiple=false', Convert::raw2xml($val)));
                    } else {
                        $src = $this->getSourceAsArray();
                        $src[$val] = $val;
                        $this->setSource($src);
                    }
                }

                $this->value = $val;
            }
        } else {
            $this->value = $val;
        }

        return $this;
        parent::setValue($value);
    }

    public function getPlaceholder()
    {
        return $this->getAttribute('data-placeholder');
    }

    public function setPlaceholder($placeholder)
    {
        return $this->setAttribute('data-placeholder', $placeholder);
    }

    public function getDisableSearch()
    {
        return $this->disable_search;
    }

    public function setDisableSearch($disable_search)
    {
        $this->disable_search = $disable_search;
    }

    public function getUseOrder()
    {
        return $this->use_order;
    }

    public function setUseOrder($use_order)
    {
        $this->use_order = $use_order;
    }

    public function getNoResultsText()
    {
        return $this->no_results_text;
    }

    public function setNoResultsText($t)
    {
        $this->no_results_text = $t;
    }

    public function getSingleDeselect()
    {
        return $this->allow_single_deselect;
    }

    public function setSingleDeselect($v)
    {
        $this->allow_single_deselect = $v;
    }

    public function getMaxSelected()
    {
        return $this->allow_max_selected;
    }

    public function setMaxSelected($max)
    {
        $this->allow_max_selected = $max;
    }

    public function getDefaultText()
    {
        return $this->getAttribute('data-placeholder');
    }

    public function setDefaultText($text)
    {
        return $this->setAttribute('data-placeholder', $text);
    }
}
