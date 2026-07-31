#!/usr/bin/env python3
"""
Generates ENTREGA-TERCERA.pdf — single PDF with all entregable content:
SRS, casos de uso, historias, matriz, codigo fuente, apendices.
"""

from html.parser import HTMLParser
from pathlib import Path
import re, sys
from fpdf import FPDF
from pygments import lex
from pygments.lexers import JavaLexer, SqlLexer
from pygments.token import Token
import markdown

ROOT = Path(__file__).resolve().parent.parent
OUTPUT = ROOT / "docs" / "entrega" / "ENTREGA-TERCERA.pdf"
FONTS_DIR = Path("C:/Windows/Fonts")

SECTIONS = []

def add_section(rel_path, title, category):
    full = ROOT / rel_path
    if full.exists():
        SECTIONS.append((rel_path, title, category, full.read_text("utf-8")))

# --- Load documents ---
add_section("docs/requisitos/SRS.md", "SRS — Documento de Requisitos", "1. SRS")

cu_map = {1:"autenticacion", 2:"gestion-libros", 3:"prestamo", 4:"devolucion", 5:"reportes"}
for i in range(1, 6):
    add_section(f"docs/requisitos/casos-de-uso/CU-0{i}-{cu_map[i]}.md",
                f"CU-0{i}: Caso de uso", "2. Casos de Uso")

hu_map = {1:"autenticacion", 2:"registro", 3:"logout", 4:"crud-usuarios",
          5:"crud-libros", 6:"prestamo", 7:"devolucion", 8:"renovacion",
          9:"reportes", 10:"dashboard"}
for i in range(1, 11):
    add_section(f"docs/requisitos/historias/HU-{i:02d}-{hu_map[i]}.md",
                f"HU-{i:02d}: Historia de usuario", "3. Historias de Usuario")

add_section("docs/trazabilidad/matriz.csv", "Matriz de Trazabilidad", "4. Matriz de Trazabilidad")

CODE_FILES = [
    ("sigcb-qr-api/src/main/java/com/sigcbqr/security/JwtTokenProvider.java", JavaLexer, "Security"),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/security/JwtAuthenticationFilter.java", JavaLexer, "Security"),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/config/SecurityConfig.java", JavaLexer, "Security"),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/security/JwtBlacklistService.java", JavaLexer, "Security"),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/controller/AuthController.java", JavaLexer, "Auth"),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/service/AuthService.java", JavaLexer, "Auth"),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/controller/PrestamoController.java", JavaLexer, "Prestamos"),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/service/PrestamoService.java", JavaLexer, "Prestamos"),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/config/CacheConfig.java", JavaLexer, "Config"),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/exception/GlobalExceptionHandler.java", JavaLexer, "Errors"),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/model/entity/Prestamo.java", JavaLexer, "Entities"),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/model/entity/Libro.java", JavaLexer, "Entities"),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/model/entity/Usuario.java", JavaLexer, "Entities"),
    ("db/procs/sp_crear_prestamo.sql", SqlLexer, "Stored Procedures"),
    ("db/procs/sp_devolver_prestamo.sql", SqlLexer, "Stored Procedures"),
    ("db/procs/sp_reporte_prestamos_diarios.sql", SqlLexer, "Stored Procedures"),
]

add_section("docs/observaciones/OBSERVACIONES.md", "Bitacora de Observaciones", "7. Apendices")
add_section("docs/requisitos/CHANGELOG-REQ.md", "CHANGELOG de Requisitos", "7. Apendices")
add_section("docs/basedatos/CATALOGO-SP.md", "Catalogo de Stored Procedures", "7. Apendices")

TOKEN_COLORS = {
    Token.Comment: (0, 128, 0),
    Token.Comment.Single: (0, 128, 0),
    Token.Comment.Multiline: (0, 128, 0),
    Token.Keyword: (0, 0, 255),
    Token.Keyword.Type: (0, 0, 255),
    Token.Keyword.Declaration: (0, 0, 255),
    Token.Keyword.Namespace: (0, 0, 255),
    Token.String: (163, 21, 21),
    Token.String.Double: (163, 21, 21),
    Token.String.Single: (163, 21, 21),
    Token.Number: (0, 0, 128),
    Token.Number.Integer: (0, 0, 128),
    Token.Number.Float: (0, 0, 128),
    Token.Name.Decorator: (128, 0, 128),
    Token.Name.Annotation: (128, 0, 128),
    Token.Name.Function: (128, 0, 0),
    Token.Name.Class: (128, 0, 128),
    Token.Name.Builtin: (0, 128, 128),
    Token.Operator: (0, 0, 0),
    Token.Punctuation: (64, 64, 64),
    Token.Text: (0, 0, 0),
    Token.Literal: (163, 21, 21),
}

def get_color(ttype):
    for tt, c in TOKEN_COLORS.items():
        if ttype in tt:
            return c
    return (0, 0, 0)

class MDParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.elements = []
        self._stack = []
        self._text = ""
        self._pre = False
        self._code_lang = ""
        self._pre_code = ""
        self._table = None
        self._tr = None

    def _take_text(self):
        t = self._text
        self._text = ""
        return t

    def handle_starttag(self, tag, attrs):
        if tag in ('h1','h2','h3','h4','h5','h6'):
            self._stack.append(('header', tag))
        elif tag == 'p':
            self._stack.append(('p',))
        elif tag in ('ul','ol'):
            self._stack.append(('list', tag))
        elif tag == 'li':
            if self._stack and self._stack[-1][0] == 'li':
                self._stack.pop()
                self.elements.append(('li', self._take_text().strip()))
            self._stack.append(('li',))
        elif tag == 'pre':
            self._pre = True
            self._pre_code = ""
        elif tag == 'code':
            if not self._pre:
                self._stack.append(('code',))
        elif tag == 'strong':
            self._stack.append(('strong',))
        elif tag == 'em':
            self._stack.append(('em',))
        elif tag == 'br':
            self._text += "\n"
        elif tag == 'hr':
            self.elements.append(('hr',))
        elif tag == 'table':
            self._table = []
        elif tag == 'tr':
            self._tr = []
        elif tag in ('td','th'):
            self._stack.append(('cell',))
        elif tag == 'blockquote':
            self._stack.append(('quote',))
        elif tag == 'a':
            for k, v in attrs:
                if k == 'href':
                    self._stack.append(('link', v))

    def handle_endtag(self, tag):
        if tag in ('h1','h2','h3','h4','h5','h6'):
            if self._stack and self._stack[-1][0] == 'header':
                self._stack.pop()
                n = int(tag[1])
                self.elements.append(('h', n, self._take_text().strip()))
        elif tag == 'p':
            if self._stack and self._stack[-1][0] == 'p':
                self._stack.pop()
                t = self._take_text().strip()
                if t:
                    self.elements.append(('p', t))
        elif tag in ('ul','ol'):
            if self._stack and self._stack[-1][0] == 'list':
                self._stack.pop()
        elif tag == 'li':
            if self._stack and self._stack[-1][0] == 'li':
                self._stack.pop()
                self.elements.append(('li', self._take_text().strip()))
        elif tag == 'pre':
            self._pre = False
            if self._pre_code.strip():
                self.elements.append(('pre', self._code_lang, self._pre_code))
            self._pre_code = ""
        elif tag == 'code':
            if not self._pre:
                if self._stack and self._stack[-1][0] == 'code':
                    self._stack.pop()
                    self.elements.append(('inline_code', self._take_text().strip()))
        elif tag == 'strong':
            if self._stack and self._stack[-1][0] == 'strong':
                self._stack.pop()
                self.elements.append(('strong', self._take_text().strip()))
        elif tag == 'em':
            if self._stack and self._stack[-1][0] == 'em':
                self._stack.pop()
                self.elements.append(('em', self._take_text().strip()))
        elif tag in ('td','th'):
            if self._stack and self._stack[-1][0] == 'cell':
                self._stack.pop()
                if self._tr is not None:
                    self._tr.append(self._take_text().strip())
        elif tag == 'tr':
            if self._tr is not None:
                if self._table is not None:
                    self._table.append(self._tr)
                self._tr = None
        elif tag == 'table':
            if self._table is not None:
                self.elements.append(('table', self._table))
            self._table = None
        elif tag == 'blockquote':
            if self._stack and self._stack[-1][0] == 'quote':
                self._stack.pop()
                self.elements.append(('quote', self._take_text().strip()))

    def handle_data(self, data):
        if self._pre:
            self._pre_code += data
        else:
            self._text += data

    def get_elements(self):
        if self._stack:
            if self._stack[-1][0] == 'li':
                self._stack.pop()
                self.elements.append(('li', self._take_text().strip()))
        return self.elements

def md_to_elements(md_text):
    html = markdown.markdown(md_text, extensions=['fenced_code', 'codehilite', 'tables', 'nl2br'])
    parser = MDParser()
    parser.feed(html)
    parser.close()
    return parser.get_elements()

def csv_to_elements(csv_text):
    lines = csv_text.strip().split('\n')
    if not lines:
        return [('p', '(empty)')]
    headers = lines[0].split(',')
    rows = [line.split(',') for line in lines[1:] if line.strip()]
    return [('table', [headers] + rows)]

class DocPDF(FPDF):
    def __init__(self):
        super().__init__(orientation="P", unit="mm", format="A4")
        self.set_auto_page_break(auto=True, margin=15)
        sans = FONTS_DIR / "DejaVuSans.ttf"
        sans_b = FONTS_DIR / "DejaVuSans-Bold.ttf"
        mono = FONTS_DIR / "DejaVuSansMono.ttf"
        mono_b = FONTS_DIR / "DejaVuSansMono-Bold.ttf"
        self.add_font("DS", "", str(sans))
        self.add_font("DS", "B", str(sans_b))
        self.add_font("DM", "", str(mono))
        self.add_font("DM", "B", str(mono_b))
        self._toc = []
        self._section_counters = {}

    def header(self):
        if self.page_no() > 1:
            self.set_font("DS", "", 7)
            self.set_text_color(140, 140, 140)
            self.cell(0, 4, "SIGCB-QR  -  Tercera Entrega  |  %d" % self.page_no(), align="R")
            self.ln(6)

    def cover_page(self):
        self.add_page()
        self.ln(50)
        self.set_font("DS", "B", 28)
        self.set_text_color(0, 51, 102)
        self.cell(0, 14, "SIGCB-QR", align="C")
        self.ln(12)
        self.set_font("DS", "", 16)
        self.set_text_color(60, 60, 60)
        self.cell(0, 9, "Sistema de Gestion de Biblioteca con Codigos QR", align="C")
        self.ln(25)
        self.set_font("DS", "B", 20)
        self.set_text_color(0, 0, 0)
        self.cell(0, 11, "ENTREGA - TERCERA ENTREGA", align="C")
        self.ln(8)
        self.set_font("DS", "", 11)
        self.set_text_color(100, 100, 100)
        self.cell(0, 7, "v0.9.0-rc  —  Julio 2026", align="C")
        self.ln(30)
        self.set_font("DS", "", 9)
        self.set_text_color(80, 80, 80)
        self.cell(0, 5, "Documento consolidado: SRS, Casos de Uso, Historias de Usuario,", align="C")
        self.ln(5)
        self.cell(0, 5, "Matriz de Trazabilidad, Codigo Fuente y Apendices", align="C")

    def toc_page(self):
        self.add_page()
        self.set_font("DS", "B", 16)
        self.set_text_color(0, 51, 102)
        self.cell(0, 10, "Tabla de Contenidos", new_x="LMARGIN")
        self.ln(12)
        self.set_font("DS", "", 10)
        self.set_text_color(0, 0, 0)
        for title, section_num in self._toc:
            indent = "    " if "." in section_num else ""
            self.cell(0, 5.5, "%s%s  %s" % (indent, section_num, title), new_x="LMARGIN")
            self.ln(5.5)

    def add_toc_entry(self, title, section_num):
        self._toc.append((title, section_num))

    def wcell(self, w, h, txt):
        self.cell(w, h, txt, new_x="LMARGIN")

    def render_elements(self, elements, indent=0):
        margin = self.l_margin + indent * 5
        col_w = self.w - margin - self.r_margin

        i = 0
        while i < len(elements):
            el = elements[i]
            t = el[0]

            # Check space before rendering
            if self.get_y() > self.h - self.b_margin - 12:
                self.add_page()

            if t == 'h':
                _, level, text = el
                sizes = {1: 16, 2: 13, 3: 11, 4: 10}
                size = sizes.get(level, 11)
                self.ln(3)
                self.set_font("DS", "B", size)
                self.set_text_color(0, 51, 102)
                self.wcell(col_w, size * 0.5, text)
                self.ln(size * 0.55)
                self.set_draw_color(0, 51, 102)
                self.set_line_width(0.3)
                if level <= 2:
                    y = self.get_y()
                    self.line(margin, y, margin + col_w, y)
                    self.ln(1)
                self.ln(2)

            elif t == 'p':
                _, text = el
                self.set_font("DS", "", 9)
                self.set_text_color(30, 30, 30)
                self.multi_cell(col_w, 4.5, text)
                self.ln(1)

            elif t == 'li':
                _, text = el
                bullet = "-"
                if i > 0 and elements[i-1][0] == 'li':
                    pass  # same list
                self.set_font("DS", "", 9)
                self.set_text_color(30, 30, 30)
                self.multi_cell(col_w, 4.5, "  %s  %s" % (bullet, text))
                self.ln(0.5)

            elif t == 'strong':
                _, text = el
                self.set_font("DS", "B", 9)
                self.set_text_color(30, 30, 30)
                if '\n' in text:
                    self.multi_cell(col_w, 4.5, text)
                    self.ln(0.5)
                else:
                    self.wcell(col_w, 4.5, text)
                    self.ln(4.5)

            elif t == 'em':
                _, text = el
                self.set_font("DS", "", 9)
                self.set_text_color(80, 80, 80)
                if '\n' in text:
                    self.multi_cell(col_w, 4.5, text)
                    self.ln(0.5)
                else:
                    self.wcell(col_w, 4.5, text)
                    self.ln(4.5)

            elif t == 'inline_code':
                _, text = el
                self.set_font("DM", "", 7.5)
                self.set_text_color(163, 21, 21)
                self.set_fill_color(245, 242, 240)
                text_w = self.get_string_width(text) + 2
                self.cell(text_w, 4.5, text, fill=True)
                self.set_font("DS", "", 9)
                self.set_text_color(30, 30, 30)

            elif t == 'pre':
                _, lang, code = el
                self.render_code_block(code.strip(), margin, col_w)

            elif t == 'quote':
                _, text = el
                self.set_font("DS", "", 8.5)
                self.set_text_color(80, 80, 80)
                self.set_fill_color(245, 245, 245)
                self.multi_cell(col_w, 4, text, fill=True)
                self.ln(1)

            elif t == 'hr':
                self.set_draw_color(180, 180, 180)
                y = self.get_y()
                self.line(margin, y, margin + col_w, y)
                self.ln(5)

            elif t == 'table':
                _, rows_data = el
                self.render_table(rows_data, margin, col_w)

            i += 1

    def render_code_block(self, code, margin, col_w):
        lexer = None
        lines = code.split('\n')
        if lines and lines[0].startswith('#'):
            # Check for language hint in markdown code block
            pass
        # Try to detect language
        try:
            lexer = JavaLexer()
            for line in lines[:5]:
                if re.search(r'\bimport\s+\w+', line) or re.search(r'@\w+', line) or re.search(r'public class', line):
                    lexer = JavaLexer()
                    break
                if re.search(r'\bCREATE\s+(OR\s+REPLACE\s+)?(FUNCTION|PROCEDURE)', line, re.IGNORECASE):
                    lexer = SqlLexer()
                    break
                if re.search(r'\bSELECT\b', line, re.IGNORECASE):
                    lexer = SqlLexer()
                    break
        except:
            lexer = None

        if not lexer:
            lexer = JavaLexer()

        self.set_font("DM", "", 6)
        self.set_fill_color(248, 248, 248)
        self.set_draw_color(200, 200, 200)

        line_w = len(str(len(lines)))
        linenum_w = line_w * 2.2 + 3
        inner_w = col_w - linenum_w - 1

        for line_no, line in enumerate(lines, 1):
            if self.get_y() > self.h - self.b_margin - 4:
                self.add_page()

            x0 = self.get_x()
            # bg
            self.set_fill_color(248, 248, 248)
            self.rect(self.l_margin, self.get_y(), col_w, 3.2, style="F")

            # line number
            self.set_text_color(160, 160, 160)
            self.wcell(linenum_w, 3.2, str(line_no).rjust(line_w))

            if line.strip() == "":
                self.ln(3.2)
                continue

            tokens = list(lex(line, lexer))
            for token_type, token_value in tokens:
                if not token_value:
                    continue
                r, g, b = get_color(token_type)
                self.set_text_color(r, g, b)
                self.wcell(self.get_string_width(token_value), 3.2, token_value)
            self.ln(3.2)

        self.ln(2)

    def render_table(self, rows, margin, col_w):
        if not rows:
            return
        ncols = max(len(r) for r in rows)
        if ncols == 0:
            return

        col_widths = [col_w / ncols] * ncols
        # Try to size columns by content
        for r in rows:
            for j, c in enumerate(r):
                if j < ncols:
                    w = self.get_string_width(c[:60]) + 4
                    if w > col_widths[j]:
                        col_widths[j] = min(w, col_w * 0.4)

        # Normalize
        total = sum(col_widths)
        if total > col_w:
            col_widths = [w * col_w / total for w in col_widths]

        self.set_font("DM", "", 6)
        self.set_draw_color(180, 180, 180)

        for ri, row in enumerate(rows):
            if self.get_y() > self.h - self.b_margin - 8:
                self.add_page()

            # Pad row
            while len(row) < ncols:
                row.append("")

            max_h = 4
            for j, cell_text in enumerate(row):
                if j < ncols:
                    lines_in_cell = max(1, len(cell_text) // 30 + 1)
                    h = lines_in_cell * 3.5
                    if h > max_h:
                        max_h = h

            if ri == 0:
                self.set_fill_color(0, 51, 102)
                self.set_text_color(255, 255, 255)
                self.set_font("DM", "B", 6)
            else:
                if ri % 2 == 0:
                    self.set_fill_color(245, 245, 250)
                else:
                    self.set_fill_color(255, 255, 255)
                self.set_text_color(30, 30, 30)
                self.set_font("DM", "", 5.5)

            x_start = self.get_x()
            y_start = self.get_y()

            for j in range(ncols):
                txt = row[j] if j < len(row) else ""
                self.set_xy(x_start + sum(col_widths[:j]), y_start)
                self.cell(col_widths[j], max_h, txt[:80], border=1, fill=True)

            self.set_xy(x_start, y_start + max_h)

        self.ln(4)

    def render_md(self, md_text):
        elements = md_to_elements(md_text)
        self.render_elements(elements)

    def render_csv(self, csv_text):
        elements = csv_to_elements(csv_text)
        self.render_elements(elements)

    def render_code_file(self, rel_path, lexer_cls):
        full = ROOT / rel_path
        if not full.exists():
            return

        self.add_page()
        self.set_font("DS", "B", 10)
        self.set_text_color(0, 51, 102)
        self.cell(0, 6, rel_path.replace("/", "\\"), new_x="LMARGIN")
        self.ln(6)

        code = full.read_text("utf-8")
        if code.endswith("\n"):
            code = code[:-1]

        lines = code.split("\n")
        line_w = len(str(len(lines)))
        margin = self.l_margin
        col_w = self.w - margin - self.r_margin
        linenum_w = line_w * 2.2 + 3

        self.set_font("DM", "", 6)
        lexer = lexer_cls()

        for line_no, line in enumerate(lines, 1):
            if self.get_y() > self.h - self.b_margin - 4:
                self.add_page()
                self.set_font("DS", "B", 10)
                self.set_text_color(0, 51, 102)
                self.cell(0, 6, rel_path.replace("/", "\\") + " (cont.)", new_x="LMARGIN")
                self.ln(5)
                self.set_font("DM", "", 6)

            self.set_fill_color(248, 248, 248)
            self.rect(self.l_margin, self.get_y(), col_w, 3.2, style="F")
            self.set_text_color(160, 160, 160)
            self.wcell(linenum_w, 3.2, str(line_no).rjust(line_w))

            if line.strip() == "":
                self.ln(3.2)
                continue

            tokens = list(lex(line, lexer))
            for token_type, token_value in tokens:
                if not token_value:
                    continue
                r, g, b = get_color(token_type)
                self.set_text_color(r, g, b)
                if token_value and self.get_string_width(token_value) > 0:
                    self.cell(self.get_string_width(token_value), 3.2, token_value)
            self.ln(3.2)

        self.ln(2)


def main():
    pdf = DocPDF()

    print("=== Portada ===")
    pdf.cover_page()
    pdf.add_toc_entry("Portada", "")

    print("=== Tabla de Contenidos ===")
    pdf.toc_page()

    current_category = None
    md_count = 0
    code_count = 0
    total_docs = len(SECTIONS) + len(CODE_FILES)

    # Markdown sections
    for rel_path, title, category, content in SECTIONS:
        md_count += 1
        if category != current_category:
            print("\n=== %s ===" % category)
            current_category = category
            pdf.add_toc_entry(category, "")

        pdf.add_toc_entry(title, "")
        print("  [%d/%d] %s" % (md_count, total_docs, rel_path))

        if rel_path.endswith(".csv"):
            pdf.add_page()
            pdf.set_font("DS", "B", 14)
            pdf.set_text_color(0, 51, 102)
            pdf.cell(0, 8, title, new_x="LMARGIN")
            pdf.ln(6)
            pdf.render_csv(content)
        else:
            pdf.add_page()
            pdf.set_font("DS", "B", 14)
            pdf.set_text_color(0, 51, 102)
            pdf.cell(0, 8, title, new_x="LMARGIN")
            pdf.ln(6)
            pdf.render_md(content)

    # Code files
    print("\n=== 5. Codigo Fuente ===")
    pdf.add_toc_entry("5. Codigo Fuente", "")
    for rel_path, lexer_cls, category in CODE_FILES:
        code_count += 1
        print("  [%d/%d] %s" % (md_count + code_count, total_docs, rel_path))
        pdf.render_code_file(rel_path, lexer_cls)

    OUTPUT.parent.mkdir(parents=True, exist_ok=True)
    pdf.output(str(OUTPUT))
    print("\n=== PDF generado: %s ===" % OUTPUT)
    print("Paginas: %d" % pdf.page_no())


if __name__ == "__main__":
    main()
