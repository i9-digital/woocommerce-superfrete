<style>
label.switch {
  display: flex;
  width: 100%;
  background: #fff;
  padding: 10px 0px;
}
  
label.switch > input { display: none; }

label.switch i {
  display: inline-block;
  float: right;
  padding: 2px;
  width: 40px;
  height: 20px;
  border-radius: 13px;
  vertical-align: middle;
  transition: .25s .09s;
  position: relative;
  background: #d8d9db;
  box-sizing: initial;
}
label.switch i:after {
  content: " ";
  display: block;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #fff;
  position: absolute;
  left: 2px;
  transition: .25s;
}
  
label.switch > input:checked + i { background: #0FAE79; }  
label.switch > input:checked + i:after { transform: translateX(20px); }  
label.switch:hover { cursor: pointer; }
/* */

#wpcontent { 
  font-family: 'Poppins', sans-serif;
  background:#fff url(@images/plugin-bg.png) no-repeat top right; 
}
.logo-super-frete {
  width: 130px;
  margin-bottom: 30px;
}
.lb-seus-tokens {
  font-size: 32px;
  font-weight: 600;
  line-height: 24px;
  margin-bottom: 16px;
  margin-top: 0;
}
.lb-seus-tokens + p {
  font-size: 16px;
  font-weight: 400;
  margin-top: 0;
  padding-top: 0;
}
.lb-titulo-token {
  background: transparent;
  width: 100%;
  float: left;
  clear: both;  
}
.lb-subtitulo-super {
  font-size: 16px;
  font-weight: 600;
}
.lb-token-sandbox {
  float: left;
  margin: 3px 12px 1px 1px;
}
.app-token {
  background: #fff;
}
.app-solicitar-token {
  float: left;
  margin-bottom: 32px;
}
.bt-solicitar-token, .bt-salvar-token {
  border: 1px solid #0FAE79;
  border-radius: 12px;
  -moz-border-radius: 12px;
  clear: both;
  float: left;
  padding: 16px 48px;
  text-decoration: none;
  font-size: 16px;
  font-weight:400px;
}
.bt-solicitar-token {
  background: #fff;
  color: #0fae79;
  box-shadow: 0px 0px 16px rgba(200, 201, 202, 0.25);
}
.bt-solicitar-token:hover { color:#008558; }
.bt-salvar-token {
  background:#0FAE79;
  color:#fff;
  margin-bottom:60px;
}
.bt-salvar-token:hover {
  background:#008558;
  cursor:pointer;
}

.tx-token {
  border-radius: 8px;
  border: 1px solid #ededed;
  color: #999999;
  height: 140px;
  width: 600px;
}
.box-token {
  float:left;
  clear:both;
  margin-bottom:32px;
}
.break-line {
  float:left;
  clear:both;
}

.obj-robot {
  position: absolute;
  top: 100px;
  right: 50px;
  max-width: 30%;  
}
.lb-link-super {
  color:#0FAE79;
  text-decoration:underline;
  font-size:16px;
  font-weight:600px;
}
.lb-link-super:hover { color:#008558;text-decoration:none; }
.tx-token-sandbox { display:block; }
.app-precisa-ajuda p {
  font-weight: 600;
  font-size: 16px;
}
@media (max-width: 1024px) {
  .obj-robot {
    position: absolute;
    top: 100px;
    right: 10px;
    max-width: 22%;    
  }
  .tx-token {
    width: 440px;
    height: 100px;    
  }
}
@media (min-width: 1024px) and (max-width: 1280px) {
  .obj-robot { max-width:24%; }
}
@media (min-width: 1280px) and (max-width: 1500px) {
  .obj-robot { max-width:22%; }
}
@media (min-width: 1500px) {
  .obj-robot { max-width:20%; }
}
</style>
<template>
  <div class="app-token">
    <h1 class="lb-titulo-token"><img src="@images/plugin-logo.png" class="logo-super-frete" /></h1>
    <img src="@images/img_robot.png" class="obj-robot" />
    <div class="app-solicitar-token">
      <h2 class="lb-seus-tokens">Seus tokens</h2>
      <p>
        Para gerar o seu token é necessário ter cadastro na SuperFrete.<br />
        Após cadastro clique no botão abaixo para solicitar o token a nossa<br />
        central de atendimento
      </p>
      <a href="https://bit.ly/superfrete-token" target="_blank" rel="noopener noreferrer" class="bt-solicitar-token">Solicitar token</a>
    </div>

    <div class="box-token">
      <h3 class="lb-subtitulo-super">Token Produção</h3>
      <textarea
        data-cy="token-production"
        v-model="token"
        placeholder="Cole o seu token de produção aqui" class="tx-token"></textarea>
    </div>

    <div class="box-token">
      <div class="box-token-titulo-sandbox">
        <label class="switch">
          <h3 class="lb-subtitulo-super lb-token-sandbox">Token Sandbox (teste)</h3>
          <input 
            type="checkbox"
            data-cy="environment-token"
            v-model="environment"
            true-value="sandbox"
            false-value="production"            
          />
          <i></i>
        </label>
      </div>
      <textarea
        v-if="environment == 'sandbox'"
        v-model="token_sandbox"
        placeholder="Cole o seu token de sandbox aqui"
        data-cy="token-sandbox" class="tx-token tx-token-sandbox"></textarea>
    </div>

    <button @click="saveToken()" class="bt-salvar-token break-line">Salvar Token</button>

    <div class="app-precisa-ajuda break-line">
      <h3 class="lb-subtitulo-super">Precisa de ajuda?</h3>
      <p>
        <a href="https://bit.ly/superfrete-ajuda" target="_blank" rel="noopener noreferrer" class="lb-link-super">Clique aqui</a> e fale conosco
      </p>
    </div>

    <div class="me-modal" v-show="show_loader">
      <svg
        style="float: left; margin-top: 10%; margin-left: 50%"
        class="ico"
        width="88"
        height="88"
        viewBox="0 0 44 44"
        xmlns="http://www.w3.org/2000/svg"
        stroke="#3598dc"
      >
        <g fill="none" fill-rule="evenodd" stroke-width="2">
          <circle cx="22" cy="22" r="1">
            <animate
              attributeName="r"
              begin="0s"
              dur="1.8s"
              values="1; 20"
              calcMode="spline"
              keyTimes="0; 1"
              keySplines="0.165, 0.84, 0.44, 1"
              repeatCount="indefinite"
            />
            <animate
              attributeName="stroke-opacity"
              begin="0s"
              dur="1.8s"
              values="1; 0"
              calcMode="spline"
              keyTimes="0; 1"
              keySplines="0.3, 0.61, 0.355, 1"
              repeatCount="indefinite"
            />
          </circle>
          <circle cx="22" cy="22" r="1">
            <animate
              attributeName="r"
              begin="-0.9s"
              dur="1.8s"
              values="1; 20"
              calcMode="spline"
              keyTimes="0; 1"
              keySplines="0.165, 0.84, 0.44, 1"
              repeatCount="indefinite"
            />
            <animate
              attributeName="stroke-opacity"
              begin="-0.9s"
              dur="1.8s"
              values="1; 0"
              calcMode="spline"
              keyTimes="0; 1"
              keySplines="0.3, 0.61, 0.355, 1"
              repeatCount="indefinite"
            />
          </circle>
        </g>
      </svg>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Router from "vue-router";

export default {
  name: "Token",
  data() {
    return {
      token: "",
      token_sandbox: "",
      environment: "production",
      show_loader: true,
    };
  },
  methods: {
    getToken() {
      this.$http
        .get(
          `${ajaxurl}?action=get_superfrete_token&_wpnonce=${wpApiSettingsSuperfrete.nonce_tokens}`
        )
        .then((response) => {
          this.token = response.data.token;
          this.token_sandbox = response.data.token_sandbox
            ? response.data.token_sandbox
            : "";
          this.environment = response.data.token_environment
            ? response.data.token_environment
            : "";
          this.show_loader = false;
        });
    },
    saveToken() {
      let bodyFormData = new FormData();
      bodyFormData.append("token", this.token);
      bodyFormData.append("token_sandbox", this.token_sandbox);
      bodyFormData.append("environment", this.environment);
      bodyFormData.append("_wpnonce", wpApiSettingsSuperfrete.nonce_tokens);
      if (
        (this.token && this.token.length > 0) ||
        (this.token_sandbox && this.token_sandbox.length > 0)
      ) {
        axios({
          url: `${ajaxurl}?action=save_superfrete_token`,
          data: bodyFormData,
          method: "POST",
        })
          .then((response) => {
            var router = new Router();
            router.push("/configuracoes");
            router.go();
          })
          .catch((err) => console.log(err));
      }
    },
  },
  mounted() {
    this.getToken();
  },
};
</script>

<style lang="css" scoped></style>
