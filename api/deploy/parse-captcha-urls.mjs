import fs from 'node:fs';
const s = fs.readFileSync(process.argv[2] ?? 'captcha.js', 'utf8');
const urls = [...s.matchAll(/https?:\/\/[a-zA-Z0-9_./?=-]+/g)].map((m) => m[0]);
const uniq = [...new Set(urls)].filter(
  (u) =>
    u.includes('eo') ||
    u.includes('gtimg') ||
    u.includes('tencent') ||
    u.includes('captcha')
);
console.log(uniq.join('\n'));