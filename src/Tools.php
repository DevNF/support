<?php

namespace NFService\Support;

use Exception;

/**
 * Classe Tools
 *
 * Classe responsável pela comunicação com a API NFService
 *
 * @category  NFService
 * @package   NFService\Common\Tools
 * @author    Diego Almeida <diego.feres82 at gmail dot com>
 * @copyright 2021 NFSERVICE
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Tools
{
    /**
     * URL base para comunicação com a API
     *
     * @var string
     */
    public static $API_URL = [
        2 => [
            1 => 'https://api.fuganholi-easy.com.br/api/nfpanel',
            2 => 'http://api.nfservice.com.br/api/nfpanel',
            3 => 'https://api.sandbox.fuganholi-easy.com.br/api/nfpanel',
            4 => 'https://api.dusk.fuganholi-easy.com.br/api/nfpanel'
        ],
        6 => [
            1 => 'https://api.fuganholi-contabil.com.br/api/nfpanel',
            2 => 'http://api.nfcontador.com.br/api/nfpanel',
            3 => 'https://api.sandbox.fuganholi-contabil.com.br/api/nfpanel',
            4 => 'https://api.dusk.fuganholi-contabil.com.br/api/nfpanel'
        ]
    ];

    /**
     * Variável responsável por armazenar os dados a serem utilizados para comunicação com a API
     * Dados como token, ambiente(produção ou homologação) e debug(true|false)
     *
     * @var array
     */
    private $config = [
        'token' => '',
        'product-id' => 0,
        'environment' => 0,
        'debug' => false,
        'upload' => false,
        'decode' => true
    ];

    /**
     * Define se a classe realizará um upload
     *
     * @param bool $isUpload Boleano para definir se é upload ou não
     *
     * @access public
     * @return void
     */
    public function setUpload(bool $isUpload) :void
    {
        $this->config['upload'] = $isUpload;
    }

    /**
     * Define se a classe realizará o decode do retorno
     *
     * @param bool $decode Boleano para definir se fa decode ou não
     *
     * @access public
     * @return void
     */
    public function setDecode(bool $decode) :void
    {
        $this->config['decode'] = $decode;
    }

    /**
     * Função responsável por definir se está em modo de debug ou não a comunicação com a API
     * Utilizado para pegar informações da requisição
     *
     * @param bool $isDebug Boleano para definir se é produção ou não
     *
     * @access public
     * @return void
     */
    public function setDebug(bool $isDebug) :void
    {
        $this->config['debug'] = $isDebug;
    }

    /**
     * Função responsável por definir o token a ser utilizado para comunicação com a API
     *
     * @param string $token Token para autenticação na API
     *
     * @access public
     * @return void
     */
    public function setToken(string $token) :void
    {
        $this->config['token'] = $token;
    }

    /**
     * Função responsável por setar o produto a ser conectado
     *
     * @param int $product_id ID do produto
     *
     * @access public
     * @return void
     */
    public function setProductId(int $product_id) :void
    {
        if (in_array($product_id, [2, 3, 4, 5, 6])) {
            $this->config['product-id'] = $product_id;
        }
    }

    /**
     * Função responsável por setar o ambiente que está sendo utilizado
     *
     * @param int $environment Ambiente 1 - Produção | 2 - Local | 3 - Sandbox | 4 - Dusk
     *
     * @access public
     * @return void
     */
    public function setEnvironment(int $environment) :void
    {
        if (in_array($environment, [1, 2, 3, 4])) {
            $this->config['environment'] = $environment;
        }
    }

    /**
     * Recupera se é upload ou não
     *
     *
     * @access public
     * @return bool
     */
    public function getUpload() : bool
    {
        return $this->config['upload'];
    }

    /**
     * Recupera se faz decode ou não
     *
     *
     * @access public
     * @return bool
     */
    public function getDecode() : bool
    {
        return $this->config['decode'];
    }

    /**
     * Recupera o product_id utilizado na comunicação com a API
     *
     * @access public
     * @return int
     */
    public function getProductId() :int
    {
        return $this->config['product-id'];
    }

    /**
     * Recupera o ambiente setado na comunicação com a API
     *
     * @access public
     * @return int
     */
    public function getEnvironment() :int
    {
        return $this->config['environment'];
    }

    /**
     * Retorna os cabeçalhos padrão para comunicação com a API
     *
     * @access private
     * @return array
     */
    private function getDefaultHeaders() :array
    {
        $headers = [
            'access-token: '.$this->config['token'],
            'Accept: application/json',
        ];

        if (!$this->config['upload']) {
            $headers[] = 'Content-Type: application/json';
        } else {
            $headers[] = 'Content-Type: multipart/form-data';
        }
        return $headers;
    }

    /**
     * Função responsável por listar os usuários de suporte
     *
     * @param array $params parametros adicionais aceitos pela rota
     *
     * @access public
     * @return array
     */
    public function listaUsuarios(array $params = []):array
    {
        try {
            $dados = $this->get('users', $params);

            if ($dados['httpCode'] == 200 || (isset($dados['body']->errors) && !empty($dados['body']->errors))) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por cadastrar um usuário no ERPClean
     *
     * @param array $dados Dados do usuário a ser cadastrado
     * @param array $params Parametros adicionais aceitos pela requisição
     *
     * @access public
     * @return array
     */
    public function cadastraUsuario(array $dados, array $params = []):array
    {
        $errors = [];
        if (!isset($dados['name']) || empty($dados['name'])) {
            $errors[] = 'Não é possível cadastrar um usuário sem o Nome';
        }
        if (!isset($dados['email']) || empty($dados['email'])) {
            $errors[] = 'Não é possível cadastrar um usuário sem o email';
        }
        if (!empty($errors)) {
            throw new Exception(implode("\r\n", $errors), 1);
        }

        try {
            $dados = $this->post('users', $dados, $params);

            if ($dados['httpCode'] == 200 || (isset($dados['body']->errors) && !empty($dados['body']->errors))) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por atualizar um usuário no ERPClean
     *
     * @param int $user_id ID do usuário no ERPClean
     * @param array $dados Dados do usuário a ser atualizado
     * @param array $params Parametros adicionais aceitos pela requisição
     *
     * @access public
     * @return array
     */
    public function atualizaUsuario(int $user_id,  array $dados, array $params = []):array
    {
        $errors = [];
        if (!isset($dados['name']) || empty($dados['name'])) {
            $errors[] = 'Não é possível atualizar um usuário sem o Nome';
        }

        try {
            $dados = $this->put("users/$user_id", $dados, $params);

            if ($dados['httpCode'] == 200 || (isset($dados['body']->errors) && !empty($dados['body']->errors))) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por gerar um token de acesso no ERPClean
     *
     * @param int $user_id ID do usuário do ERPClean que realizará o acesso
     * @param int $customer_id ID da empresa no ERPClean
     * @param array $params Parametros adicionais aceitos pela requisição
     *
     * @access public
     * @return array
     */
    public function geraTokenAcesso(int $user_id, int $customer_id, array $params = []):array
    {
        try {
            $dados = [
                'customer_id' => $customer_id
            ];

            $dados = $this->post("users/$user_id/token", $dados, $params);

            if ($dados['httpCode'] == 200 || (isset($dados['body']->errors) && !empty($dados['body']->errors))) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (\Exception $error) {
            throw new Exception($error, 1);
        }
    }

      /**
     * Função responsável por listar bancos com integração VAN
     *
     * @param array $params parametros adicionais aceitos pela rota
     *
     * @access public
     * @return array
     */
    public function listaIntegracaoVAN(int $customer_id, array $params = []):array
    {
        try {
            $dados = $this->get("banks/{$customer_id}", $params);

            if ($dados['httpCode'] == 200 || (isset($dados['body']->errors) && !empty($dados['body']->errors))) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por atualizar status de integração VAN
     *
     * @param array $params parametros adicionais aceitos pela rota
     *
     * @access public
     * @return array
     */
    public function atualizaStatusIntegracaoVAN(int $customer_id, int $status, int $letter_id, array $params = []):array
    {
        try {
            $dados = $this->post("banks/{$customer_id}/van/{$letter_id}/update-status", [
                'status' => $status
            ], $params);

            if ($dados['httpCode'] == 200 || (isset($dados['body']->errors) && !empty($dados['body']->errors))) {
                return $dados;
            }

            throw new Exception($dados['body']->message, 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Execute a GET Request
     *
     * @param string $path
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     *
     * @access protected
     * @return array
     */
    protected function get(string $path, array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_HTTPHEADER => $this->getDefaultHeaders()
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER], $headers);
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a POST Request
     *
     * @param string $path
     * @param string $body
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     *
     * @access protected
     * @return array
     */
    protected function post(string $path, array $body = [], array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => !$this->config['upload'] ? json_encode($body) : $body,
            CURLOPT_HTTPHEADER => $this->getDefaultHeaders()
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER], $headers);
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a PUT Request
     *
     * @param string $path
     * @param string $body
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     *
     * @access protected
     * @return array
     */
    protected function put(string $path, array $body = [], array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_HTTPHEADER => $this->getDefaultHeaders(),
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => json_encode($body)
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER], $headers);
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a DELETE Request
     *
     * @param string $path
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     *
     * @access protected
     * @return array
     */
    protected function delete(string $path, array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_HTTPHEADER => $this->getDefaultHeaders(),
            CURLOPT_CUSTOMREQUEST => "DELETE"
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER], $headers);
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a OPTION Request
     *
     * @param string $path
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     *
     * @access protected
     * @return array
     */
    protected function options(string $path, array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_CUSTOMREQUEST => "OPTIONS"
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = $headers;
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Função responsável por realizar a requisição e devolver os dados
     *
     * @param string $path Rota a ser acessada
     * @param array $opts Opções do CURL
     * @param array $params Parametros query a serem passados para requisição
     *
     * @access protected
     * @return array
     */
    protected function execute(string $path, array $opts = [], array $params = []) :array
    {
        if (!preg_match("/^\//", $path)) {
            $path = '/' . $path;
        }

        $url = self::$API_URL[$this->config['product-id']][$this->config['environment']].$path;

        $curlC = curl_init();

        if (!empty($opts)) {
            curl_setopt_array($curlC, $opts);
        }

        if (!empty($params)) {
            $paramsJoined = [];

            foreach ($params as $param) {
                if (isset($param['name']) && !empty($param['name']) && isset($param['value']) && !empty($param['value'])) {
                    $paramsJoined[] = urlencode($param['name'])."=".urlencode($param['value']);
                }
            }

            if (!empty($paramsJoined)) {
                $params = '?'.implode('&', $paramsJoined);
                $url = $url.$params;
            }
        }

        curl_setopt($curlC, CURLOPT_URL, $url);
        curl_setopt($curlC, CURLOPT_RETURNTRANSFER, true);
        if (!empty($dados)) {
            curl_setopt($curlC, CURLOPT_POSTFIELDS, json_encode($dados));
        }
        $retorno = curl_exec($curlC);
        $info = curl_getinfo($curlC);
        $return["body"] = ($this->config['decode'] || !$this->config['decode'] && $info['http_code'] != '200') ? json_decode($retorno) : $retorno;
        $return["httpCode"] = curl_getinfo($curlC, CURLINFO_HTTP_CODE);
        if ($this->config['debug']) {
            $return['info'] = curl_getinfo($curlC);
        }
        curl_close($curlC);

        return $return;
    }
}
