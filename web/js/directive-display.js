

class DirectiveDisplay extends HTMLElement {


  constructor() {
    super();
    var span = document.createElement('span');
    span.slot="id";
    span.innerHTML = "<h4></h4>"
    const root = this.attachShadow({mode: 'open'});
    this.fillSlot = this.fillSlot.bind(this);
  }

  connectedCallback() {
    const template = DirectiveDisplay.template;
    this.shadowRoot.appendChild(template.content.cloneNode(true));
  }

  fillSlot(slotName, slotValue) {
    var span = document.createElement("span");
    span.slot = slotName;
    span.innerHTML = slotValue
    this.appendChild(span);
  }

}
DirectiveDisplay.template = document.createElement('template');
DirectiveDisplay.template.innerHTML = `
<style>
  .row {
      float: none;
      clear: both;
      position: relative;
  }
  .row .col {
      float: left;
      display: inline;
      border-top: 1px solid #dfdfdf;
      padding: .25rem 1rem;
      min-height: 1.5rem;
      max-height: 1.5rem;
      min-width: 1rem;
      max-width: 3rem;
  }
  .row .col.first {
    text-align: left;
  }
  .row .col.second {
    text-align: center;
  }
  .row .col.third {
    text-align: right;
    position: absolute;
    top: -5px;
    left: 10em;
    border-top: none;
  }
  .row .topRight {
    position: absolute;
    top: 0px;
    right: 0px;
  }
</style>
    <div class="row">
      <span class="col first"><slot name="name"></slot></span>
      <span class="col second"><slot name="version"></slot></span>
      <span class="col third"><slot name="enabled">➕</slot></span>
      <span class="topRight"><slot name="display"></slot></span>
    </div>
  `;

customElements.define("directive-display", DirectiveDisplay);
