// Writes a .br and a .gz copy of every compressible file under a directory, for nginx's
// brotli_static / gzip_static. Usage: node precompress.mjs public/build
import { readdirSync, readFileSync, statSync, writeFileSync } from 'node:fs';
import { extname, join } from 'node:path';
import { brotliCompressSync, constants, gzipSync } from 'node:zlib';

const compressible = new Set(['.css', '.js', '.json', '.svg', '.html', '.txt', '.xml']);
const minimumBytes = 1024;

function walk(dir) {
    for (const name of readdirSync(dir)) {
        const path = join(dir, name);
        if (statSync(path).isDirectory()) {
            walk(path);
            continue;
        }
        if (!compressible.has(extname(path)) || statSync(path).size < minimumBytes) {
            continue;
        }
        const body = readFileSync(path);
        writeFileSync(`${path}.br`, brotliCompressSync(body, { params: { [constants.BROTLI_PARAM_QUALITY]: 11 } }));
        writeFileSync(`${path}.gz`, gzipSync(body, { level: 9 }));
    }
}

walk(process.argv[2] ?? 'public/build');
