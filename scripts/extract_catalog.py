import openpyxl
import json
import os
from collections import OrderedDict

xlsx_path = os.path.join(os.path.dirname(__file__), '..', 'Catalogue_Le_Paradou_Pressing_Blanchisserie_Lavage_au_kilo.xlsx')
output_json_path = os.path.join(os.path.dirname(__file__), '..', 'storage', 'app', 'catalog_extracted.json')

wb = openpyxl.load_workbook(xlsx_path, data_only=True)
sheet = wb['CATALOGUE COMPLET']
rows = list(sheet.iter_rows(values_only=True))[1:]

# Target definitions and orders
TARGET_CONFIG = OrderedDict([
    ('Homme', {'name': 'Homme', 'sort': 1}),
    ('Femme', {'name': 'Femme', 'sort': 2}),
    ('Enfant 3–12 ans', {'name': 'Enfant 3–12 ans', 'sort': 3}),
    ('Bébé', {'name': 'Bébé', 'sort': 4}),
    ('Cuir / daim / matières spéciales', {'name': 'Cuir & Daim', 'sort': 5}),
    ('Général', {'name': 'Linge de maison', 'sort': 6}),
])

articles_map = OrderedDict()

for r in rows:
    if not any(r): continue
    srv, cat, subcat, art, grammage = r[0], r[1], r[2], r[3], r[4] if len(r) > 4 else None
    
    cat = (cat.strip() if cat else '')
    subcat = (subcat.strip() if subcat else '')
    art = (art.strip() if art else '')
    
    if not art: continue
    
    key = (cat, art)
    if key not in articles_map:
        target_info = TARGET_CONFIG.get(cat, {'name': cat, 'sort': 99})
        articles_map[key] = {
            'original_category': cat,
            'target_name': target_info['name'],
            'target_sort': target_info['sort'],
            'subcategory_name': '',
            'article_name': art,
            'standard_weight': None,
            'is_carpet': False,
            'unit_type': 'piece',
            'services': []
        }
        
    if subcat and subcat != 'Référence' and not articles_map[key]['subcategory_name']:
        articles_map[key]['subcategory_name'] = subcat
        
    if grammage and articles_map[key]['standard_weight'] is None:
        try:
            articles_map[key]['standard_weight'] = int(grammage)
        except:
            articles_map[key]['standard_weight'] = None
            
    if srv and srv not in articles_map[key]['services']:
        articles_map[key]['services'].append(srv)
        
    # Check carpet
    if articles_map[key]['subcategory_name'] == 'Tapis' or 'tapis' in art.lower():
        articles_map[key]['is_carpet'] = True
        articles_map[key]['unit_type'] = 'm2'

# Gather targets, subcategories, and articles
targets = []
for orig_cat, cfg in TARGET_CONFIG.items():
    targets.append({
        'original_key': orig_cat,
        'name': cfg['name'],
        'sort_order': cfg['sort']
    })

# Gather unique subcategories per target
subcategories_map = OrderedDict()
for item in articles_map.values():
    t_name = item['target_name']
    sc_name = item['subcategory_name']
    if sc_name:
        subcategories_map[(t_name, sc_name)] = True

subcategories = []
for (t_name, sc_name) in subcategories_map.keys():
    subcategories.append({
        'target_name': t_name,
        'name': sc_name
    })

articles_list = list(articles_map.values())

payload = {
    'metadata': {
        'total_articles': len(articles_list),
        'total_targets': len(targets),
        'total_subcategories': len(subcategories),
        'source_file': 'Catalogue_Le_Paradou_Pressing_Blanchisserie_Lavage_au_kilo.xlsx'
    },
    'targets': targets,
    'subcategories': subcategories,
    'articles': articles_list
}

with open(output_json_path, 'w', encoding='utf-8') as f:
    json.dump(payload, f, ensure_ascii=False, indent=2)

print(f"Successfully extracted {len(articles_list)} articles, {len(subcategories)} subcategories into {output_json_path}")
