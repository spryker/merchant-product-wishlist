<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Client\MerchantProductWishlist\Expander;

use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\WishlistItemCollectionTransfer;
use Generated\Shared\Transfer\WishlistMoveToCartRequestCollectionTransfer;

class MerchantProductWishlistExpander implements MerchantProductWishlistExpanderInterface
{
    public function expandWishlistItemCollectionTransfer(
        WishlistMoveToCartRequestCollectionTransfer $wishlistMoveToCartRequestCollectionTransfer,
        WishlistMoveToCartRequestCollectionTransfer $failedWishlistMoveToCartRequestCollectionTransfer,
        WishlistItemCollectionTransfer $wishlistItemCollectionTransfer
    ): WishlistItemCollectionTransfer {
        $failedMerchantReferences = [];

        foreach ($failedWishlistMoveToCartRequestCollectionTransfer->getRequests() as $failedMoveToCartRequestTransfer) {
            $failedMerchantReferences[] = $failedMoveToCartRequestTransfer->getMerchantReference();
        }

        foreach ($wishlistMoveToCartRequestCollectionTransfer->getRequests() as $moveToCartRequestTransfer) {
            if (in_array($moveToCartRequestTransfer->getMerchantReference(), $failedMerchantReferences)) {
                continue;
            }

            /** @var \Generated\Shared\Transfer\WishlistItemTransfer $wishlistItemTransfer */
            $wishlistItemTransfer = $moveToCartRequestTransfer->getWishlistItem();
            $wishlistItemCollectionTransfer->addItem($wishlistItemTransfer);
        }

        return $wishlistItemCollectionTransfer;
    }

    public function expandWishlistMoveToCartRequestCollectionTransfer(
        WishlistMoveToCartRequestCollectionTransfer $wishlistMoveToCartRequestCollectionTransfer,
        QuoteTransfer $quoteTransfer,
        WishlistMoveToCartRequestCollectionTransfer $wishlistMoveToCartRequestCollectionDiffTransfer
    ): WishlistMoveToCartRequestCollectionTransfer {
        $merchantReferenceIndex = [];
        /** @var array<\Generated\Shared\Transfer\ItemTransfer> $itemTransfers */
        $itemTransfers = array_merge($quoteTransfer->getItems()->getArrayCopy(), $quoteTransfer->getBundleItems()->getArrayCopy());

        foreach ($itemTransfers as $itemTransfer) {
            if (!$itemTransfer->getMerchantReference()) {
                continue;
            }

            $merchantReferenceIndex[$itemTransfer->getMerchantReference()] = true;
        }

        if (!$merchantReferenceIndex) {
            return $wishlistMoveToCartRequestCollectionDiffTransfer;
        }

        foreach ($wishlistMoveToCartRequestCollectionTransfer->getRequests() as $wishlistMoveToCartRequestTransfer) {
            if (isset($merchantReferenceIndex[$wishlistMoveToCartRequestTransfer->getMerchantReference()])) {
                continue;
            }

            $wishlistMoveToCartRequestCollectionDiffTransfer->addRequest($wishlistMoveToCartRequestTransfer);
        }

        return $wishlistMoveToCartRequestCollectionDiffTransfer;
    }
}
