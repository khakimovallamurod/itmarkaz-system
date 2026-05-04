import logging
import os
import mysql.connector
from dotenv import load_dotenv
from telegram import Update
from telegram.ext import ApplicationBuilder, CommandHandler, ContextTypes

# Load environment variables
load_dotenv()

# Database Configuration
DB_HOST = os.getenv("DB_HOST", "localhost")
DB_USER = os.getenv("DB_USER", "root")
DB_PASS = os.getenv("DB_PASS", "")
DB_NAME = os.getenv("DB_NAME", "itmarkazdb")
BOT_TOKEN = os.getenv("BOT_TOKEN")

# Logging setup
logging.basicConfig(
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    level=logging.INFO
)

def get_db_connection():
    return mysql.connector.connect(
        host=DB_HOST,
        user=DB_USER,
        password=DB_PASS,
        database=DB_NAME
    )

async def start(update: Update, context: ContextTypes.DEFAULT_TYPE):
    user = update.effective_user
    chat_id = str(user.id)
    first_name = user.first_name
    last_name = user.last_name
    username = user.username

    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        
        # Insert or Update user info in itmarkaz_bot table
        query = """
        INSERT INTO itmarkaz_bot (chat_id, first_name, last_name, username)
        VALUES (%s, %s, %s, %s)
        ON DUPLICATE KEY UPDATE 
            first_name = VALUES(first_name),
            last_name = VALUES(last_name),
            username = VALUES(username)
        """
        cursor.execute(query, (chat_id, first_name, last_name, username))
        conn.commit()
        cursor.close()
        conn.close()

        message = (
            f"👋 <b>Assalomu alaykum, {first_name}!</b>\n\n"
            f"🆔 Sizning Telegram ID: <code>{chat_id}</code>\n"
            f"👤 Username: @{username if username else '-'}\n\n"
            "Ma'lumotlaringiz muvaffaqiyatli saqlandi."
        )
        await update.message.reply_text(message, parse_mode='HTML')
        
    except Exception as e:
        await update.message.reply_text("❌ Xatolik yuz berdi. Iltimos keyinroq urinib ko'ring.")

if __name__ == '__main__':
    if not BOT_TOKEN:
        exit(1)

    application = ApplicationBuilder().token(BOT_TOKEN).build()
    
    start_handler = CommandHandler('start', start)
    application.add_handler(start_handler)
    
    application.run_polling()
