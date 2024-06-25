
module my_addr::MyNFT {
    use std::string;
    use std::vector;
    use aptos_framework::account;
    use aptos_framework::event;
    use aptos_framework::timestamp;
    use aptos_token::token;

    struct MyNFT has key {
        token_data_id: token::TokenDataId,
        minting_events: event::EventHandle<MintingEvent>,
    }

    struct MintingEvent has drop, store {
        token_id: token::TokenId,
        timestamp: u64,
    }

    public entry fun initialize(account: &signer) {
        let collection_name = string::utf8(b"MyNFTCollection");
        let description = string::utf8(b"A collection of my awesome NFTs");
        let collection_uri = string::utf8(b"https://example.com/my-nft-collection");
        let maximum_supply = 1000;
        let mutate_setting = vector::empty<bool>();

        token::create_collection(
            account,
            collection_name,
            description,
            collection_uri,
            maximum_supply,
            mutate_setting
        );

        move_to(account, MyNFT {
            token_data_id: token::create_tokendata(
                account,
                collection_name,
                string::utf8(b"MyNFT"),
                string::utf8(b"My awesome NFT"),
                0,
                string::utf8(b"https://example.com/my-nft"),
                @my_addr,
                1,
                0,
                token::create_token_mutability_config(&vector::empty<bool>()),
                vector::empty<string::String>(),
                vector::empty<vector<u8>>(),
                vector::empty<string::String>(),
            ),
            minting_events: account::new_event_handle<MintingEvent>(account),
        });
    }

    public entry fun mint_nft(account: &signer) acquires MyNFT {
        let nft = borrow_global_mut<MyNFT>(@my_addr);
        let token_id = token::mint_token(account, nft.token_data_id, 1);
        event::emit_event(&mut nft.minting_events, MintingEvent {
            token_id,
            timestamp: timestamp::now_microseconds(),
        });
    }
}

