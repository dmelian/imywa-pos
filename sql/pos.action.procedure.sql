delimiter $$

drop procedure if exists action_display$$
create procedure action_display()
begin
	-- Este caso está diseñado para locales con un único tpv. (ej:glamour)
	declare istate varchar(12);
	select state into istate from sale order by saleNo DESC limit 1;

	CASE istate
		WHEN 'draft' THEN
			select 0 as error, 'bill::priceView::delete' as message;
		WHEN 'deleted' THEN
			select 0 as error, 'new::empty::priceView' as message;
		WHEN 'billed' THEN
			select 0 as error, 'pay::priceView::revise' as message;
		WHEN 'revised' THEN
			select 0 as error, 'bill::empty::priceView' as message;
		WHEN 'paid' THEN
			select 0 as error, 'new::priceView::anull' as message;
		WHEN 'anulled' THEN
			select 0 as error, 'new::empty::priceView' as message;
	END CASE;
end$$

delimiter ;
