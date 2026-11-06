/**
 * Holds the JS grammar to the fixture file the PHP parser in wp-super-duper uses.
 *
 * Usage:  node --test tests/js/dynamic-data-grammar.test.js
 * The fixture is looked up next door in wp-super-duper; set SD_GRAMMAR_FIXTURES to override.
 */
const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const grammar = require('../../assets/libs/universal-icon-picker/js/dynamic-data-grammar.js');

const fixturePath = process.env.SD_GRAMMAR_FIXTURES
    || path.resolve(__dirname, '../../../wp-super-duper/tests/fixtures/dynamic-data-grammar.json');
const fixtures = JSON.parse(fs.readFileSync(fixturePath, 'utf8'));

// PHP decodes an empty JSON object as an empty array; compare plain objects here.
const plain = (v) => JSON.parse(JSON.stringify(v));

for (const c of fixtures.parse) {
    test(`parse ${c.input}`, () => {
        assert.deepEqual(plain(grammar.parse(c.input)), c.expect);
    });
}

for (const c of fixtures.build) {
    test(`build ${c.expect}`, () => {
        const built = grammar.build(c.name, c.primary, c.args);
        assert.equal(built, c.expect);

        const args = Object.fromEntries(Object.entries(c.args).filter(([, v]) => v !== false));
        assert.deepEqual(plain(grammar.parse(built)), { name: c.name, primary: c.primary, args });
    });
}

for (const c of fixtures.find) {
    test(`find ${JSON.stringify(c.input)}`, () => {
        assert.deepEqual(grammar.find(c.input).map((m) => m.raw), c.expect);
    });
}

test('findAt locates the tag around the caret', () => {
    const text = 'Hi {post_title @link} and {site_title}';
    assert.equal(grammar.findAt(text, 5).raw, '{post_title @link}');
    assert.equal(grammar.findAt(text, 21).raw, '{post_title @link}');
    assert.equal(grammar.findAt(text, 23), null);
    assert.equal(grammar.findAt(text, 3), null);
});
