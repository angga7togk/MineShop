<?php

namespace angga7togk\mineshop\utils;

use pocketmine\item\Item;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\TreeRoot;

class Utils
{

	public static function serializeItem(Item $item): string
	{
		$serializer = new BigEndianNbtSerializer();
		$nbtData = $serializer->write(new TreeRoot($item->nbtSerialize()));
		return base64_encode(zlib_encode($nbtData, ZLIB_ENCODING_GZIP));
	}


	public static function deserializeItem(string $data): Item
	{
		$decodedData = zlib_decode(base64_decode($data));
		if ($decodedData === false) {
			throw new \RuntimeException("Failed to decode item data.");
		}
		$serializer = new BigEndianNbtSerializer();
		return Item::nbtDeserialize($serializer->read($decodedData)->mustGetCompoundTag());
	}
}
