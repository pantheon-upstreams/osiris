



class PhpVersion extends HTMLElement {

  get src() {
    return this.getAttribute("data-src");
  }

  set src(value) {
    this.setAttribute('data-src', value);
  }

  get version() {
    return this.getAttribute('data-version');
  }

  set version(value) {
    this.setAttribute('version', value)
  }

  constructor() {
    super();
    this.handleResponse = this.handleResponse.bind(this);
  }

  connectedCallback() {
    const root = this.attachShadow({mode: 'open'});
    root.appendChild(PhpVersion.template.content.cloneNode(true));
    var cachebuster = Math.round(new Date().getTime() / 1000),
      myFetchUrl = "//".concat(this.src, "?cachebuster=", cachebuster)
    fetch(myFetchUrl, {
      headers: {
        'Accept': 'application/json',
      }
    })
      .then(res => res.json())
      .then(ajaxData => this.handleResponse(ajaxData))
      .catch(e => {
        console.log("There was a problem receiving the JSON from the instance:", e);
        //TODO: better error handling
      });
  }

  handleResponse(ajaxData) {
    var versionContainer = document.createElement("div");
    versionContainer.class = "container-fluid";
    this.shadowRoot.querySelector('#php-version-value').innerHTML = ajaxData['modules']['core']['php version'];
    for (const module in ajaxData['modules']) {
      var directiveDisplay = document.createElement("directive-display");
      directiveDisplay.id = module;
      directiveDisplay.fillSlot('name', module);
      if (ajaxData['modules'][module]['version']) {
        directiveDisplay.fillSlot('version', ajaxData['modules'][module]['version']);
      }
      switch(module) {
        case "imagick":
          directiveDisplay.fillSlot('display', ajaxData['imagick']);
          break;

        case "gd":
          directiveDisplay.fillSlot('display', ajaxData['gd']);
          break;

        default:
          if (ajaxData['modules'][module][module.concat(' support')]) {
            const enabled = ajaxData['modules'][module][module.concat(' support')] ? "✅" : "❌";
            directiveDisplay.fillSlot('enabled', enabled);
          }
      }
      versionContainer.appendChild(directiveDisplay);
    }
    this.shadowRoot.appendChild(versionContainer);
    console.info("response handled", this);
  }

}

PhpVersion.template = document.createElement('template');
PhpVersion.template.innerHTML = `
    <div style="margin-top: 3em;">
        <h1 id='php-version-value'><slot name="version">Version</slot></h1>
    </div>`

customElements.define("php-version", PhpVersion);
