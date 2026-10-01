const fs = require("fs");
const sharp = require("../node_modules/sharp");
const photos = [
  [
    "scarf",
    3038031,
    "Woman wearing a beige scarf — Arif Isomoto",
    "https://www.pexels.com/photo/woman-wearing-beige-scarf-3038031/",
  ],
  [
    "rose",
    6070103,
    "Silk scarf with wildflowers",
    "https://www.pexels.com/photo/stylish-silk-scarf-placed-on-table-with-bunches-of-delicate-flowers-6070103/",
  ],
  [
    "journal",
    1119792,
    "Pen and notebook",
    "https://www.pexels.com/photo/pen-and-notebook-1119792/",
  ],
  [
    "mat",
    30948665,
    "Prayer beads on an Islamic mat",
    "https://www.pexels.com/photo/heart-shaped-prayer-beads-on-islamic-mat-30948665/",
  ],
  [
    "beads",
    36771510,
    "Prayer beads on a colourful rug",
    "https://www.pexels.com/photo/close-up-of-prayer-beads-on-colorful-rug-36771510/",
  ],
  [
    "ceramic",
    7302760,
    "Ceramic incense holder",
    "https://www.pexels.com/photo/ceramic-incense-holder-7302760/",
  ],
  [
    "gift",
    5485173,
    "Presents on a beige background",
    "https://www.pexels.com/photo/presents-on-a-beige-background-5485173/",
  ],
  [
    "notes",
    8250927,
    "Notebook, pen and eraser",
    "https://www.pexels.com/photo/pen-and-eraser-lying-next-to-notebook-8250927/",
  ],
];
(async () => {
  for (const [name, id] of photos) {
    const r = await fetch(
      `https://images.pexels.com/photos/${id}/pexels-photo-${id}.jpeg?auto=compress&cs=tinysrgb&w=1600`,
    );
    if (!r.ok) throw Error(`${name}: ${r.status}`);
    const b = Buffer.from(await r.arrayBuffer());
    await sharp(b)
      .resize({ width: 900, withoutEnlargement: true })
      .webp({ quality: 72 })
      .toFile(`${__dirname}/theme/images/${name}.webp`);
    console.log(name);
  }
  fs.writeFileSync(
    `${__dirname}/images.json`,
    JSON.stringify(
      photos.map(([name, id, title, source]) => ({
        name,
        title,
        source,
        license: "Pexels License",
        licenseUrl: "https://www.pexels.com/license/",
      })),
      null,
      2,
    ),
  );
})();
