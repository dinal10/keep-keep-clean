const observer = new IntersectionObserver(
  entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add("is-visible");
        observer.unobserve(entry.target);
      }
    });
  },
  {
    threshold: 0.16,
    rootMargin: "0px 0px -40px 0px",
  }
);

document.querySelectorAll("[data-reveal]").forEach(element => {
  observer.observe(element);
});

const formatCurrency = value =>
  new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0,
  }).format(value);

const estimatorForm = document.querySelector("[data-estimator-form]");

if (estimatorForm) {
  const totalNode = document.querySelector("[data-estimate-total]");
  const breakdownNode = document.querySelector("[data-estimate-breakdown]");
  const serviceSelect = estimatorForm.querySelector("[name='service']");
  const itemCountInput = estimatorForm.querySelector("[name='itemCount']");
  const areaSelect = estimatorForm.querySelector("[name='area']");
  const shoeTypeSelect = estimatorForm.querySelector("[name='shoeType']");
  const extras = Array.from(estimatorForm.querySelectorAll("input[name='extras']"));
  const phone = estimatorForm.dataset.phone || "6281382197099";
  const pricing = {};
  Array.from(serviceSelect.options).forEach(option => {
    pricing[option.value] = {
      label: option.dataset.title || option.textContent,
      base: Number(option.dataset.price || 0),
    };
  });

  const areaFees = {
    local: 0,
    west: 25000,
    jakarta: 50000,
    custom: 75000,
  };

  const shoeMultipliers = {
    standard: 1,
    premium: 1.35,
    repair: 1.7,
  };

  const extraFees = {
    deodorizer: { label: "Deodorizer", price: 25000 },
    antiBacterial: { label: "Anti-bacterial finish", price: 35000 },
    express: { label: "Priority slot", price: 50000 },
  };

  const syncEstimator = () => {
    const service = pricing[serviceSelect.value];
    const itemCount = Math.max(1, Number(itemCountInput.value || 1));
    const areaFee = areaFees[areaSelect.value] || 0;
    const selectedExtras = extras.filter(input => input.checked);

    let subtotal = service.base * itemCount;
    let notes = [`${service.label} x ${itemCount}`];

    if (serviceSelect.value === "shoe") {
      const multiplier = shoeMultipliers[shoeTypeSelect.value] || 1;
      subtotal = Math.round(subtotal * multiplier);
      notes.push(`Tipe sepatu: ${shoeTypeSelect.options[shoeTypeSelect.selectedIndex].text}`);
    }

    if (serviceSelect.value === "disinfectant") {
      notes = [`${service.label} x 1 kunjungan`];
    }

    const extrasTotal = selectedExtras.reduce((sum, extra) => {
      notes.push(extraFees[extra.value].label);
      return sum + extraFees[extra.value].price;
    }, 0);

    const total = subtotal + extrasTotal + areaFee;
    const breakdown = [
      `Layanan: ${formatCurrency(subtotal)}`,
      `Tambahan: ${formatCurrency(extrasTotal)}`,
      `Area: ${formatCurrency(areaFee)}`,
    ];

    totalNode.textContent = formatCurrency(total);
    breakdownNode.textContent = `${breakdown.join(" / ")} / Detail: ${notes.join(", ")}`;
  };

  serviceSelect.addEventListener("change", syncEstimator);
  itemCountInput.addEventListener("input", syncEstimator);
  areaSelect.addEventListener("change", syncEstimator);
  shoeTypeSelect.addEventListener("change", syncEstimator);
  extras.forEach(input => input.addEventListener("change", syncEstimator));

  estimatorForm.addEventListener("submit", event => {
    event.preventDefault();

    const formData = new FormData(estimatorForm);
    const name = formData.get("name") || "-";
    const service = pricing[formData.get("service")];
    const itemCount = formData.get("itemCount") || "1";
    const area = estimatorForm.querySelector("[name='area']").selectedOptions[0].text;
    const schedule = formData.get("schedule") || "-";
    const details = formData.get("details") || "-";
    const selectedExtras = formData.getAll("extras").map(value => extraFees[value].label);
    const shoeType = estimatorForm.querySelector("[name='shoeType']").selectedOptions[0].text;

    const message = [
      "Halo Keepkeepclean, saya ingin booking layanan.",
      "",
      `Nama: ${name}`,
      `Layanan: ${service.label}`,
      `Jumlah item/pasang: ${itemCount}`,
      `Area: ${area}`,
      `Jadwal yang diinginkan: ${schedule}`,
      `Tipe shoe care: ${serviceSelect.value === "shoe" ? shoeType : "-"}`,
      `Tambahan: ${selectedExtras.length ? selectedExtras.join(", ") : "-"}`,
      `Catatan: ${details}`,
      `Estimasi website: ${totalNode.textContent}`,
    ].join("\n");

    window.open(`https://wa.me/${encodeURIComponent(phone)}?text=${encodeURIComponent(message)}`, "_blank");
  });

  syncEstimator();
}
