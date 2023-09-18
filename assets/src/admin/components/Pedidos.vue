<style>
.lb-titulo-superfrete {
  font-size: 32px !important;
  font-weight: 600 !important;
  line-height: 24px !important;
  margin-bottom: 16px !important;
  margin-top: 0 !important;
}

.boxBanner {
  float: left;
  width: 100%;
  margin-bottom: 15px;
}

.boxBanner img {
  float: left;
  width: 100%;
}

.lineGray:nth-child(odd) {
  background: #e1e1e1;
}

.text-center {
  text-align: center;
}

.styleTableMoreInfo span {
  font-size: 16px;
}

.styleTableMoreInfo td {
  padding: 1%;
}

.error-message {
  width: 98%;
  padding: 10px 0 10px 2%;
  font-weight: 600;
}

.me-modal {
  z-index: 1 !important;
}

.me-modal .title {
  padding:0px 0px 15px 0px !important;
  margin:0 !important;
}
.me-modal .content .txt {
    font-size: 16px !important;
    line-height: 1.2em !important;
    margin-bottom: 15px !important;
    font-weight: 400 !important;
    font-family: Poppins,sans-serif !important;
    margin:0 !important;
}

.btn-modal {
    font-family: Poppins,sans-serif !important;
    font-size: 16px !important;
    font-weight: 400 !important;
    line-height: 30px !important;
    padding: 12px 40px !important;
    width: 245px !important;
    height: 56px !important;  
}

.me-modal-2 {
  z-index: 1 !important;
}

.scrollBox {
  overflow: auto;
  height: 50px;
}

.superfrete-style a {
    color: #0fae79 !important;
}

.btn-border {
    background: #0FAE79 !important;
    border-color: #0FAE79 !important;
    color: #fff !important;
}
.btn-border:hover {
    background-color: transparent !important;
    color: #0FAE79 !important;
}

.me-modal {
  text-align: center !important;
  padding-top: 15px !important;
  padding-bottom: 15px !important;
}
</style>

<template>
  <div class="app-pedidos superfrete-style">
    <div class="boxBanner">
      <img src="@images/banner-admin.jpeg" />
    </div>
    <template>
      <div>
        <div class="grid">
          <div class="col-12-12">
            <h1 class="lb-titulo-superfrete">Meus pedidos</h1>
          </div>
          <hr />
          <div class="col-12-12" v-show="true">
            <p class="error-message">{{ error_message }}</p>
          </div>
        </div>
      </div>
    </template>

    <div style="display: flex" class="container-table-box">
      <table border="0" class="table-box" style="width:40% !important;">
        <tr>
          <td>
            <h4>
              <b>Usuário:</b>
              {{ name }}
            </h4>
            <h4>
              <b>Ambiente:</b>
              {{ environment }}
            </h4>
            <h4>
              <b>Envios:</b>
              {{ getLimitEnabled }}/{{ getLimit }}
            </h4>
            <h4>
              <b>Saldo:</b>
              {{ getBalance }}
            </h4>
          </td>
        </tr>
      </table>
      <table
        style="margin-left: 15px; width: 100%"
        border="0"
        class="table-box"
      >
        <tbody>
          <tr>
            <td>
              <p>
                <ul>
                  <li style="margin-bottom: 15px">
                    <b>1.</b> Caso não tenha saldo, será necessário realizar a
                    recarga via PIX, pela guia "Perfil" do aplicativo ou endereço
                    <a href="https://web.superfrete.com" target="_blank"
                      >https://web.superfrete.com</a
                    >;
                  </li>
                  <li style="margin-bottom: 15px">
                    <b>2.</b> O pagamento da etiqueta também pode ser efetuado
                    diretamente pelo aplicativo, via cartão de crédito, através da
                    guia "Etiquetas", selecione a etiqueta desejada, com o status
                    "Aguardando pagamento", e após isso, clique em "Emitir Frete",
                    para efetuar o pagamento;
                  </li>
                  <li style="margin-bottom: 15px">
                    <b>3.</b> Após confirmado o pagamento, será possível realizar
                    a impressão da etiqueta, através do ícone de impressora;
                  </li>
                  <li style="margin-bottom: 15px">
                    <b>4.</b> Pronto! Seu pedido não tem mais nenhuma pendência,
                    basta levar até a agência dos correios mais próxima.
                  </li>
                </ul>
              </p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div
      class="table-box"
      v-if="orders.length > 0"
      :class="{ '-inative': !orders.length }"
    >
      <div class="table -woocommerce">
        <ul class="head">
          <li><img src="@images/reload-icon.png" style="width:24px;height:24px;cursor:pointer;margin-top:3px;" @click="reloadPage" /></li>
          <li>
            <span>ID</span>
          </li>
          <li>
            <span>Destinatário</span>
          </li>
          <li>
            <span>Cotação</span>
          </li>
          <li>
            <span>Documentos</span>
          </li>
          <!--
          <li>
            <span>Etiqueta</span>
          </li>
          -->
          <li>
            <span>Ações</span>
          </li>
        </ul>

        <ul class="body">
          <li
            v-for="(item, index) in orders"
            :key="index"
            class="lineGray"
            style="padding: 1%"
          >
            <ul class="body-list">
              <li>
                <span></span>
              </li>
              <li>
                <Id :item="item"></Id>
              </li>
              <li>
                <Destino :to="item.to"></Destino>
              </li>
              <li>
                <template v-if="item.products">
                  <label>Produto</label>
                  <div class="scrollBox">
                    <p v-for="product in item.products">
                      {{ product.quantity }}x
                      <ProductLink :id="product.id" :name="product.name" />
                    </p>
                  </div>
                </template>
                <Cotacao :item="item"></Cotacao>
                <template v-if="item.protocol && item.status != null">
                  <p>
                    Protocolo:
                    <b>{{ item.protocol }}</b>
                  </p>
                  <p v-if="item.tracking != null">
                    Rastreio:
                    <ProductLink
                      :definedLink="item.link_tracking"
                      :name="item.tracking"
                    />
                  </p>
                </template>
              </li>
              <li>
                <Documentos :item="item"></Documentos>
              </li>
              <li class="-center">
                <Acoes :item="item"></Acoes>
              </li>
            </ul>
            <template v-if="toggleInfo == item.id">
              <informacoes
                :volume="
                  item.quotation[item.quotation.choose_method].volumes[0]
                "
                :products="item.products"
              ></informacoes>
            </template>
          </li>
        </ul>
      </div>
    </div>
    <div v-else>
      <p>Nenhum registro encontrado</p>
    </div>
    <button
      v-show="show_more"
      class="btn-border -full-green"
      @click="loadMore({ status: status, wpstatus: wpstatus })"
    >
      Carregar mais
    </button>

    <transition name="fade">
      <!-- show_modal error -->
      <div class="me-modal me-modal-2" v-show="show_modal_error || show_modal2">
        <div style="padding:15px !important">
          <img src="@images/icon-modal-error.png" alt="" />
          <p class="title">SuperFrete</p>
          <div class="content">
            <p v-for="msg in msg_modal_error" class="txt">{{ msg }}</p>
            <p v-for="msg in msg_modal2" class="txt">{{ msg }}</p>
          </div>
          <div class="buttons -center">
            <button
              v-if="btnCloseError"
              type="button"
              @click="close"
              class="btn-border btn-modal"
            >
              Fechar
            </button>
          </div>
        </div>
      </div>
      </transition>
      
      <transition name="fade">
      <!-- show_modal -->
      <div class="me-modal me-modal-2" v-show="show_modal || show_modal2">
        <div style="padding:15px !important">
          <img src="@images/icon-modal-success.png" alt="" />
          <p class="title">SuperFrete</p>
          <div class="content">
            <p v-for="msg in msg_modal" class="txt">{{ msg }}</p>
            <p v-for="msg in msg_modal2" class="txt">{{ msg }}</p>
          </div>
          <div class="buttons -center">
            <button
              v-if="btnClose"
              type="button"
              @click="close"
              class="btn-border btn-modal"
            >
              Fechar
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- show_loader -->
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
import { mapActions, mapGetters } from "vuex";
import Id from "./Pedido/Id.vue";
import Destino from "./Pedido/Destino.vue";
import Cotacao from "./Pedido/Cotacao.vue";
import Documentos from "./Pedido/Documentos.vue";
import Acoes from "./Pedido/Acoes.vue";
import ProductLink from "./ProductLink.vue";
import Informacoes from "./Pedido/Informacoes.vue";
import {
  verifyToken,
  getToken,
  isDateTokenExpired,
} from "admin/utils/token-utils";

export default {
  name: "Pedidos",
  data: () => {
    return {
      status: "all",
      wpstatus: "all",
      line: 0,
      toggleInfo: null,
      error_message: null,
      orderSelecteds: [],
      allSelected: false,
      name: null,
      environment: null,
      limit: 0,
      limitEnabled: 0,
      totalOrders: 0,
      totalCart: 0,
      show_modal2: false,
      msg_modal2: [],
      btnClose: true,
      btnCloseError: true,
    };
  },
  components: {
    Id,
    Cotacao,
    Destino,
    Documentos,
    Acoes,
    Informacoes,
    ProductLink,
  },
  computed: {
    ...mapGetters("orders", {
      orders: "getOrders",
      show_loader: "toggleLoader",
      msg_modal: "setMsgModal",
      msg_modal_error: "setMsgModalError",
      show_modal: "showModal",
      show_modal_error: "showModalError",
      show_more: "showMore",
      statusWooCommerce: "statusWooCommerce",
    }),
    ...mapGetters("balance", ["getLimitEnabled", "getLimit", "getBalance"]),
  },
  methods: {
    ...mapActions("orders", [
      "retrieveMany",
      "loadMore",
      "closeModal",
      "getStatusWooCommerce",
      "printMultiples",
      "updateQuotation",
      "addCart",
      "showErrorAlert",
    ]),
    ...mapActions("balance", ["setBalance", "setLimits"]),
    close() {
      this.closeModal();
    },
    handleToggleInfo(id) {
      this.toggleInfo = this.toggleInfo != id ? id : null;
    },
    getToken() {
      this.$http.get(verifyToken()).then((response) => {
        if (!response.data.exists_token) {
          this.$router.push("Token");
        }

        this.validateToken();
      });
    },
    selectAll: function () {
      if (!this.$refs.selectAllBox.checked) {
        this.orderSelecteds = [];
        this.orders.filter((order) => {
          this.$refs[order.id][0].checked = false;
        });
        return;
      }
      let selecteds = [];
      this.orders.filter((order) => {
        selecteds.push(order);
        this.$refs[order.id][0].checked = true;
      });
      this.orderSelecteds = selecteds;
    },
    beforePrintMultiples: function () {
      this.msg_modal2.length = 0;
      let selecteds = [];
      let not = [];
      let messagePrint = [];

      this.orders.filter((order) => {
        if (
          this.$refs[order.id][0].checked &&
          (order.status == "posted" ||
            order.status == "released" ||
            order.status == "paid" ||
            order.status == "generated" ||
            order.status == "printed")
        ) {
          selecteds.push(order.id);
        }

        if (order.status == null) {
          this.$refs[order.id][0].checked = false;
          not.push(order.id);
        }
      });

      if (selecteds.length == 0) {
        this.msg_modal2.push("Nenhuma etiqueta disponível para imprimir");
        this.show_modal2 = true;
        return;
      }

      this.orderSelecteds = selecteds;
      this.notCanPrint = not;

      this.msg_modal2.length = 0;
      this.printMultiples({
        orderSelecteds: selecteds,
        message: messagePrint[0],
      });
    },
    alertMessage: function (data) {
      let stringMessage;
      data.filter((item) => {
        this.msg_modal2.push(item);
      });
      this.show_modal2 = true;
    },
    getSelectedOrders() {
      const orders = [];
      this.orders.filter((order) => {
        if (this.$refs[order.id][0].checked && order.status == null) {
          orders.push(order);
        }
      });
      return orders;
    },
    async beforeBuyOrders() {
      this.show_modal2 = true;
      this.btnClose = false;
      this.btnCloseError = false;
      const orderSelected = this.getSelectedOrders();

      if (orderSelected.length == 0) {
        this.show_modal2 = false;
        this.msg_modal2.length = 0;
        return;
      }

      for (const idx in orderSelected) {
        await this.dispatchCart(orderSelected[idx]);
      }
      this.btnClose = true;
      this.btnCloseError = true;
    },
    countOrdersEnabledToBuy: function () {
      let total = 0;
      this.orders.filter((order) => {
        if (this.$refs[order.id][0].checked && order.status == null) {
          total++;
        }
      });
      return total;
    },

    dispatchCart: function (order) {
      this.msg_modal2.push("Enviando pedido ID" + order.id + ". Aguarde ...");

      return new Promise((resolve, reject) => {
        let data = {
          id: order.id,
          choosen: order.quotation.choose_method,
          non_commercial: order.non_commercial,
        };

        setTimeout(() => {
          this.addCart(data)
            .then((response) => {
              this.msg_modal2.push(
                "Pedido ID" + order.id + " enviado com sucesso!"
              );
              resolve(response);
            })
            .catch((error) => {
              this.msg_modal2.push(
                "OPS!, ocorreu um erro ao enviar o pedido ID" + order.id
              );
              this.btnClose = true;
              this.btnCloseError = true;
              error.errors.filter((item) => {
                this.msg_modal2.push("ID:" + order.id + ": " + item);
              });
              this.btnClose = true;
              this.btnCloseError = true;
              resolve();
            });
        }, 100);
      });
    },
    getMe() {
      this.$http
        .get(
          `${ajaxurl}?action=superfrete_me&_wpnonce=${wpApiSettingsSuperfrete.nonce_users}`
        )
        .then((response) => {
          if (response.data.id) {
            this.name = response.data.firstname + " " + response.data.lastname;
            this.environment = response.data.environment;
            ///this.limit = response.data.limits.shipments;
            ///this.limitEnabled = response.data.limits.shipments_available;
          }
        });
    },
    close() {
      this.show_modal2 = false;
      this.msg_modal2.length = 0;
      this.closeModal();
    },
    validateToken() {
      this.$http.get(getToken()).then((response) => {
        if (response.data.token) {
          if (isDateTokenExpired(response.data.token)) {
            this.error_message =
              "Seu Token SuperFrete expirou, cadastre um novo token para o plugin voltar a funcionar perfeitamente";
          } else {
            this.error_message = "";
          }
        } else {
          this.$router.push("Token");
        }
      });
    },
    reloadPage() {
      window.location.reload();
    },
  },
  watch: {
    status() {
      this.retrieveMany({ status: this.status, wpstatus: this.wpstatus });
    },
    wpstatus() {
      this.retrieveMany({ status: this.status, wpstatus: this.wpstatus });
    },
  },
  mounted() {
    this.getToken();
    this.getMe();
    this.setLimits();
    if (Object.keys(this.orders).length === 0) {
      this.retrieveMany({ status: this.status, wpstatus: this.wpstatus });
    }
    this.setBalance();
    this.getStatusWooCommerce();
  },
};
</script>

<style lang="css" scoped></style>
