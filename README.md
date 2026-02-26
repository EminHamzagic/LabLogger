# Blockchain Lab Logger - WordPress Plugin

A WordPress plugin for logging physics and science experiment data with blockchain verification using Solana. Records are stored in WordPress and verified on the Solana blockchain for data integrity.

## 🎯 Features

- **Blockchain Verification**: Log experiment data to Solana blockchain for immutable record-keeping
- **Data Integrity**: Verify that experiment data hasn't been tampered with
- **Easy to Use**: Simple form interface for students to log experiments
- **Admin Dashboard**: View and manage all experiments
- **Helius Integration**: Uses Helius RPC for reliable Solana connectivity
- **Devnet Support**: Free testing environment perfect for educational use
- **Timestamp Proof**: Each experiment gets a blockchain timestamp
- **Explorer Links**: Direct links to view transactions on Solana Explorer

## 📋 Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- Node.js 18+ (for backend)
- A Solana wallet
- Helius API key (free tier available)

## 🚀 Installation

### Step 1: Install the Plugin

1. Upload the `blockchain-lab-logger` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. You'll see a new "Lab Logger" menu in the admin sidebar

### Step 2: Backend Service

The plugin is connected to a dedicated Node.js backend:

- **Backend repository**: `https://github.com/EminHamzagic/solana-logger-backend`
- The backend URL is **fixed in the plugin code** (`BLL_Blockchain_Client`) and is not configurable from the WordPress admin.
- The backend is responsible for talking to Solana/Helius, holding the wallet key and Helius API key, and exposing a simple REST API that the plugin calls.

If you want to run or modify the backend yourself (for development or self‑hosting), clone and configure the backend repo:

```bash
git clone https://github.com/EminHamzagic/solana-logger-backend.git
cd solana-logger-backend
npm install
cp .env.example .env
# edit .env with your Helius API key and wallet configuration
npm start
```

> Note: In the default setup of this plugin, the backend URL is hardcoded and there is **no backend URL field** in WordPress settings.

### Step 3: Add to Your Site

Add the shortcode to any page or post:

```
[lab_logger]
```

Or with options:

```
[lab_logger title="Physics Lab 101" show_history="yes"]
```

## 💡 Usage

### For Students

1. Visit a page with the lab logger form
2. Fill in experiment details:
    - Experiment name
    - Student name (optional)
    - Measurements (can add multiple)
    - Observations/notes
3. Click "Log Experiment to Blockchain"
4. Receive confirmation with:
    - Unique experiment ID
    - Blockchain transaction signature
    - Link to Solana Explorer

### For Teachers

1. View all experiments in **Lab Logger → All Experiments**
2. Click "View Details" to see verification status
3. Click "Explorer →" to view blockchain transaction
4. Verify data integrity at any time

## 🔍 Data Verification

To verify experiment data hasn't been tampered with:

1. Click the **Verify** button next to any experiment
2. The system will:
    - Recalculate the data hash
    - Compare to original hash
    - Verify blockchain transaction
    - Compare blockchain hash
3. Results show:
    - ✅ **INTACT**: Data matches blockchain record
    - ❌ **COMPROMISED**: Data has been altered

## 🎓 How It Works

### The Process

1. **Data Entry**: Student enters experiment data
2. **Hash Calculation**: System creates SHA-256 hash of data
3. **Blockchain Logging**: Hash sent to Solana via backend
4. **Transaction Confirmation**: Solana returns transaction signature
5. **Database Storage**: WordPress saves data + signature

### Why Blockchain?

- **Immutability**: Once written, cannot be changed
- **Timestamp**: Proves when data was recorded
- **Transparency**: Anyone can verify on blockchain explorer
- **Data Integrity**: Even if WordPress is compromised, blockchain record remains

### Security

- Only the **hash** (fingerprint) is stored on blockchain
- Actual experiment data stays in WordPress database
- Anyone with both can verify integrity
- Blockchain data is public but actual data is private

## ⚙️ Configuration

### Shortcode Options

| Option         | Description             | Default                 |
| -------------- | ----------------------- | ----------------------- |
| `title`        | Form title              | "Lab Experiment Logger" |
| `show_history` | Show recent experiments | "yes"                   |

Examples:

```
[lab_logger]
[lab_logger title="Chemistry Lab"]
[lab_logger show_history="no"]
[lab_logger title="Physics 101" show_history="yes"]
```

## 💰 Costs

### Devnet (Development)

- **FREE!** Perfect for testing and student projects
- Unlimited transactions
- Free SOL from faucet

### Mainnet (Production)

- ~0.000005 SOL per transaction
- At $100/SOL = $0.0005 per log entry
- 1000 experiments = **$0.50**
- Very affordable for production use

## 🔧 Troubleshooting

### Backend Connection Failed

- Ensure backend is running (`npm start`)
- Check Backend URL in Settings
- Verify firewall settings
- Check backend console for errors

### Transaction Failed

- Check wallet balance (need at least 0.01 SOL)
- Request airdrop from faucet
- Verify Helius API key
- Check Solana network status

### Form Not Showing

- Verify shortcode: `[lab_logger]`
- Check plugin is activated
- Clear WordPress cache
- Check browser console for errors

### Low Wallet Balance

- Request more SOL from [Solana Faucet](https://faucet.solana.com)
- May need multiple requests
- Check balance: `solana balance -k wallet.json --url devnet`

## 🛡️ Security Best Practices

### For Development (Devnet)

- Use test wallets only
- Never use mainnet keys on devnet
- Rotate Helius API keys periodically

### For Production (Mainnet)

- Use dedicated wallet for logging
- Keep minimal balance (0.1-1 SOL)
- Store wallet.json securely
- Use environment variables for sensitive data
- Never commit wallet.json to version control
- Add authentication to backend endpoints
- Use HTTPS for production
- Implement rate limiting
- Regular security audits

## 📚 Resources

- **Solana Documentation**: [docs.solana.com](https://docs.solana.com)
- **Helius Documentation**: [docs.helius.dev](https://docs.helius.dev)
- **Solana Explorer**: [explorer.solana.com](https://explorer.solana.com)
- **Devnet Faucet**: [faucet.solana.com](https://faucet.solana.com)
- **Solana Web3.js**: [solana-labs.github.io/solana-web3.js](https://solana-labs.github.io/solana-web3.js)

## 🤝 Contributing

This project was created for a MultiMedial Systems course project. Contributions are welcome!

## 📄 License

GPL v2 or later

## 🎓 Educational Use

This plugin is designed for educational purposes to demonstrate:

- Blockchain technology in practice
- Data integrity and verification
- Decentralized Science (DeSci) concepts
- Web3 integration with traditional web applications
- Cryptographic hashing
- Immutable record-keeping

Perfect for:

- Physics labs
- Chemistry experiments
- Engineering projects
- Science courses
- Blockchain education
- Computer science demonstrations

## ⚠️ Important Notes

1. **Blockchain data is permanent** - Once logged, it cannot be deleted
2. **Use Devnet for testing** - Free and safe for learning
3. **Backup your wallet** - Save the seed phrase securely
4. **Monitor balance** - Request airdrops when low
5. **Test thoroughly** - Verify everything works on Devnet before production
6. **Privacy considerations** - Only hash is public, but still be mindful
7. **Cost management** - Even on mainnet, costs are very low

## 📞 Support

For issues or questions:

1. Check the **How to Use** page in the plugin
2. Review troubleshooting section above
3. Check backend console logs
4. Verify Solana network status
5. Test with a fresh wallet

## 🔮 Future Enhancements

Potential future features:

- Export experiments to CSV/Excel
- Student authentication with wallet signatures
- Token rewards for logging experiments
- Advanced data visualization
- Multi-wallet support
- Batch verification
- Email notifications
- Custom fields configuration
- Data analytics dashboard
- Mobile app integration

## 📊 Technical Stack

- **Frontend**: WordPress, PHP, jQuery
- **Backend**: Node.js, Express
- **Blockchain**: Solana (Devnet/Mainnet)
- **RPC Provider**: Helius
- **Database**: WordPress MySQL
- **Hashing**: SHA-256
- **API**: RESTful

---

Made with ❤️ for science and education
