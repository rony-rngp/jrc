import json
import sqlite3
import os

db_path = "database/database.sqlite"
if os.path.exists(db_path):
    os.remove(db_path)

conn = sqlite3.connect(db_path)
cursor = conn.cursor()

with open("database/dump.json", "r") as f:
    dump = json.load(f)

for table_name, data in dump.items():
    cols = data["columns"]
    rows = data["rows"]
    
    col_defs = []
    
    for c in cols:
        name = f'"{c["Field"]}"'
        col_type = c["Type"].lower()
        if "int" in col_type:
            sql_type = "INTEGER"
        elif "decimal" in col_type or "float" in col_type or "double" in col_type:
            sql_type = "REAL"
        else:
            sql_type = "TEXT"
            
        if c.get("Key") == "PRI" and "auto_increment" in c.get("Extra", "").lower():
            col_defs.append(f"{name} INTEGER PRIMARY KEY AUTOINCREMENT")
        elif sql_type in ("INTEGER", "REAL"):
            col_defs.append(f"{name} {sql_type} DEFAULT 0")
        else:
            col_defs.append(f"{name} {sql_type} DEFAULT NULL")
            
    cols_str = ", ".join(col_defs)
    create_stmt = f'CREATE TABLE IF NOT EXISTS "{table_name}" ({cols_str})'
    cursor.execute(create_stmt)
    
    if rows:
        col_names = list(rows[0].keys())
        quoted_cols = [f'"{col}"' for col in col_names]
        placeholders = ["?" for _ in col_names]
        cols_joined = ",".join(quoted_cols)
        places_joined = ",".join(placeholders)
        insert_stmt = f'INSERT OR IGNORE INTO "{table_name}" ({cols_joined}) VALUES ({places_joined})'
        
        row_tuples = [tuple(r[col] for col in col_names) for r in rows]
        cursor.executemany(insert_stmt, row_tuples)
        
    print(f"Loaded {table_name} ({len(rows)} rows)")

conn.commit()
conn.close()
print("Successfully generated database/database.sqlite!")
