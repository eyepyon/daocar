
from aptos_sdk.account import Account
from aptos_sdk.client import RestClient
from aptos_sdk.transactions import TransactionArgument, TransactionPayload

# Aptosクライアントの初期化
client = RestClient("https://fullnode.devnet.aptoslabs.com/v1")

# アカウントの設定
private_key = "YOUR_PRIVATE_KEY_HERE"
account = Account.load_key(private_key)

# NFTコレクションの初期化
def initialize_nft_collection():
    payload = TransactionPayload(
        function=f"{account.address()}::MyNFT::initialize",
        type_arguments=[],
        arguments=[]
    )

    tx = client.create_bcs_transaction(account, payload)
    client.submit_bcs_transaction(tx)
    client.wait_for_transaction(tx.hash)
    print("NFT collection initialized")

# NFTのミント
def mint_nft():
    payload = TransactionPayload(
        function=f"{account.address()}::MyNFT::mint_nft",
        type_arguments=[],
        arguments=[]
    )

    tx = client.create_bcs_transaction(account, payload)
    client.submit_bcs_transaction(tx)
    client.wait_for_transaction(tx.hash)
    print("NFT minted successfully")

# メイン実行
if __name__ == "__main__":
    initialize_nft_collection()
    mint_nft()

