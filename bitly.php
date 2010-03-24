<?php
/**
 * Classe PHP para utilização da API RESTful do Bit.ly
 * 
 * @author Igor Escobar (blog@igorescobar.com)
 * 
 * ----- ATENCAO -----
 * 
 * Se possível, sempre trabalhar com o formato de retorno em JSON. 
 * O PHP possuí nativamente as funções para tratamento destes dados.
 * Caso você precise do retorno em XML esta library vai retornar o
 * arquivo inteiro em XML e você irá precisar tratá-lo com um XML Parser
 * de sua escolha.  
 *
 * @functions shorten
 * @functions expand
 * @functions info
 * @functions stats
 * @functions debug
 * 
 * @uses  
 * 
 * 		Se você preencheu fixo na API o login e a API-Key, você pode usar assim:
 * 
 * 		$bitly 		= new Bitly();
 *		$bitly->url = 'http://www.google.com/';
 *		$bitly->shorten(); 
 *
 *		$bitly->getData()->shortUrl; #saída: http://bit.ly/b6R4Uf
 *		$bitly->getData()->userHash; #saída: b6R4Uf
 * 		
 * @uses 
 * 
 * 		Se você não preencheu ou prefere informar os dados de Login e API-Key, use assim:
 * 
 * 			$bitly 		= new Bitly();
 *			$bitly->url = 'http://bit.ly/b6R4Uf';
 *			$bitly->expand();
 *	
 *			echo $bitly->getData()->longUrl; # saída: http://www.google.com/
 *
 */

class Bitly {
	
	/**
	 * Versão da API que você quer utilizar.
	 *
	 * @var string
	 */
	
	public $version = '2.0.1';
	
	/**
	 * Login que foi cadastrado para acesso na API do Bit.ly
	 *
	 * @var string
	 */
	
	public $login = 'login_bitly';
	
	/**
	 * Chave gerada para acesso à API do Bit.ly
	 *
	 * @var string
	 */
	
	public $api_key = 'api_key';
	
	/**
	 * Formato de saída da API.
	 *
	 * @uses JSON
	 * @uses XML
	 * @var string
	 */
	
	public $format = 'json';
	
	/**
	 * Função de callback. É opcional. Caso queira utilizar, basta preencher o nome da função que quer chamar.
	 *
	 * @var string
	 */
	
	public $callback;
	
	/**
	 * Url que você quer trabalhar utilizando a API do Bit.ly
	 *
	 * @var string
	 */
	
	public $url;
	
	/**
	 * Variável de controle para ser usada quando a API já foi invocada
	 *
	 * @var boolean
	 */
	
	protected $active = false;
	
	/**
	 * Variável de controle de ser usada quando o acesso a API falhar. 
	 *
	 * @var boolean
	 */
	
	protected $fail = false;
	
	/**
	 * Ação que a biblioteca vai executar
	 *
	 * @var string
	 */
	
	protected $action = null;
	
	/**
	 * Função responsável por encurtar a url diretamente no Bit.Ly
	 *
	 * @return void
	 * @author Igor Escobar
	 */
	
	public function action () {
		
		/**
		 * Cria a query que será enviada para a API do Bit.ly
		 */
		
		$params = http_build_query ( array(
			'version'	=> $this->version,
			'login'		=> $this->login,
			'apiKey'	=> $this->api_key,
			'longUrl'	=> $this->url,
			'shortUrl'	=> $this->url,		
			'format'	=> $this->format,
			'callback'	=> $this->callback
		) );
		
		/**
		 * Faz a requisição na API do Bit.ly
		 */
		
		$this->return = file_get_contents ( 'http://api.bit.ly/' . $this->action . '?' . $params );
		
	}
	
	/**
	 * Executa o Shorten do Bit.ly
	 *
	 * @author Igor Escobar
	 */
	
	public function shorten () {
		
		$this->action = 'shorten';
		
		$this->action();
				
	}
	
	/**
	 * Executa o Expand do Bit.ly
	 *
	 * @author Igor Escobar
	 */
	
	public function expand () {
		
		$this->action = 'expand';
		
		$this->action();
		
	}
	
	/**
	 * Executa o Info do Bit.ly
	 *
	 * @author Igor Escobar
	 */
	
	public function info () {
		
		$this->action = 'info';
		
		$this->action();
		
	}
	
	/**
	 * Executa o Stats do Bit.ly
	 *
	 * @author Igor Escobar
	 */
	
	public function stats () {
		
		$this->action = 'stats';
		
		$this->action();
		
	}
	
	/**
	 * Converte os dados da requisição na API
	 *
	 * @return void
	 * @author Igor Escobar
	 */
	
	public function get (){
		   
		$this->active = true;
		
		 if( $this->format == 'json' ) {
			$this->return = json_decode($this->return);
		
		} 
		
	}
	
	/**
	 * Retorna um aparametro especifico dentro do pacote que a API retornou
	 *
	 * @return void
	 * @author Igor Escobar
	 */
	
	public function getData() {
		
		
		if ( $this->active == false )
			$this->get ();
	         
		 if ( $this->format == 'json' ) {

        	if ( property_exists ( $this->return, 'results' ) ) {
				    
				/**
				 * Em determinadas ocasioes o bit.ly retorna o array com uma estrutura
				 * diferente. Para isso eu tive que programar uma solução que pega
				 * sempre o primeiro parametro do objeto como o node de partida.
				 */
				
				$ar_object_vars = get_object_vars ($this->return->results);
				$ar_object_keys = array_keys($ar_object_vars);
				$node = $ar_object_keys[0];
				
				/**
				 * Quando utilizamos o Stats do Bitly o retorno possui uma estrutura
				 * diferente de todos os outros metodos. 
				 */
				
				if($this->action != 'stats' )
		 			return 	$this->return->results->$node;
				else
					return $this->return->results;
		
			} else {
				
				// Trava o acesso as propriedades da API
				$this->fail = true;
				
				// Ativa o debug
				$this->debug();
			}
         
		 /**
		  * Se você precisa que o retorno seja em XML irei retornar o XML puro para que você trate
		  * com um XML-Parser de sua preferencia. 
		  */
		
		 } elseif ( $formato == 'xml' ) {
         
		 	return $this->return;
         
		 }
		
	}
	
	/**
	 * Se algo estiver dando errado, chame esta função para descobrir
	 * o que a API está retornando.
	 *
	 * @return void
	 * @author Igor Escobar
	 */
	
	public function debug () {
		
		echo "<pre>"; 
		print_r( $this->return ); 
		echo "</pre>";
		
	}
	
}
?>