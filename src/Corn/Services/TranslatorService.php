<?php

namespace Corn\Services;
use Pimple\Container;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\LocaleSwitcher;


class TranslatorService{
	private $app;
    private $translator;

    public function __construct(Container $app)
    {
		
        $this->app = $app;
        /*
        $this->translator = new Translator('fr_FR');       
		$this->translator->addLoader('array', new ArrayLoader());
		$this->translator->addResource('array', [
			'Hello World!' => 'Bonjour !',
		], 'fr_FR');
		*/ 
		$this->translator = new Translator('id_ID');     
		$this->translator->addLoader('yaml', new YamlFileLoader());
		$this->translator->addResource('yaml', __DIR__.'/../locales/id.yml', 'id');
    }
    
    public function trans($text, array $parameters = array()){
		return $this->translator->trans($text, $parameters);
	}
}
